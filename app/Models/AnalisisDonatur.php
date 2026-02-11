<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AnalisisDonatur extends Model
{
    protected $table = 'laporans';

    // Field yang akan digunakan
    protected $appends = [
        'total_donasi',
        'jml_transaksi', 
        'first_donation',
        'last_donation',
    ];

    /**
     * Scope untuk mendapatkan donatur unik dengan agregasi
     */
    public function scopeDonaturUnik($query)
    {
        return $query->select(
            'no_hp',
            DB::raw('MAX(nama_donatur) as nama_donatur'),
            DB::raw('SUM(jml_perolehan) as total_donasi'),
            DB::raw('COUNT(*) as jml_transaksi'),
            DB::raw('MIN(tanggal) as first_donation'),
            DB::raw('MAX(tanggal) as last_donation')
        )
        ->whereNotNull('no_hp')
        ->where('no_hp', '!=', '')
        ->groupBy('no_hp');
    }

    /**
     * Scope untuk filter tahun tertentu
     */
    public function scopeTahun($query, $year)
    {
        return $query->whereYear('tanggal', $year);
    }

    /**
     * Scope untuk top donatur (berdasarkan total donasi)
     */
    public function scopeTopDonatur($query, $year = null)
    {
        $q = $query->donaturUnik();
        if ($year) {
            $q->whereYear('tanggal', $year);
        }
        return $q->orderByDesc('total_donasi');
    }

    /**
     * Scope untuk donatur yang hilang (donasi tahun lalu, tidak di tahun ini)
     */
    public function scopeDonaturHilang($query, $currentYear = 2025)
    {
        $donaturTahunIni = DB::table('laporans')
            ->whereYear('tanggal', $currentYear)
            ->whereNotNull('no_hp')
            ->where('no_hp', '!=', '')
            ->distinct()
            ->pluck('no_hp');

        return $query->select(
            'no_hp',
            DB::raw('MAX(nama_donatur) as nama_donatur'),
            DB::raw('SUM(jml_perolehan) as total_donasi'),
            DB::raw('COUNT(*) as jml_transaksi'),
            DB::raw('MIN(tanggal) as first_donation'),
            DB::raw('MAX(tanggal) as last_donation')
        )
        ->whereYear('tanggal', '<', $currentYear)
        ->whereNotNull('no_hp')
        ->where('no_hp', '!=', '')
        ->whereNotIn('no_hp', $donaturTahunIni)
        ->groupBy('no_hp');
    }

    /**
     * Scope untuk donatur baru (pertama kali donasi di tahun tertentu)
     */
    public function scopeDonaturBaru($query, $currentYear = 2025)
    {
        $donaturSebelumnya = DB::table('laporans')
            ->whereYear('tanggal', '<', $currentYear)
            ->whereNotNull('no_hp')
            ->where('no_hp', '!=', '')
            ->distinct()
            ->pluck('no_hp');

        return $query->select(
            'no_hp',
            DB::raw('MAX(nama_donatur) as nama_donatur'),
            DB::raw('SUM(jml_perolehan) as total_donasi'),
            DB::raw('COUNT(*) as jml_transaksi'),
            DB::raw('MIN(tanggal) as first_donation'),
            DB::raw('MAX(tanggal) as last_donation')
        )
        ->whereYear('tanggal', $currentYear)
        ->whereNotNull('no_hp')
        ->where('no_hp', '!=', '')
        ->whereNotIn('no_hp', $donaturSebelumnya)
        ->groupBy('no_hp');
    }

    /**
     * Scope untuk donatur tidak aktif dalam periode tertentu
     * @param int $days - jumlah hari tidak aktif
     */
    public function scopeTidakAktif($query, $days = 30)
    {
        $cutoffDate = now()->subDays($days)->format('Y-m-d');

        return $query->select(
            'no_hp',
            DB::raw('MAX(nama_donatur) as nama_donatur'),
            DB::raw('SUM(jml_perolehan) as total_donasi'),
            DB::raw('COUNT(*) as jml_transaksi'),
            DB::raw('MIN(tanggal) as first_donation'),
            DB::raw('MAX(tanggal) as last_donation')
        )
        ->whereNotNull('no_hp')
        ->where('no_hp', '!=', '')
        ->groupBy('no_hp')
        ->havingRaw('MAX(tanggal) < ?', [$cutoffDate]);
    }

    /**
     * Mendapatkan riwayat donasi donatur berdasarkan no_hp
     */
    public static function getRiwayatDonasi($noHp)
    {
        return DB::table('laporans')
            ->where('no_hp', $noHp)
            ->orderByDesc('tanggal')
            ->get();
    }

    /**
     * Mendapatkan data profil donatur dari tabel donaturs
     */
    public static function getProfilDonatur($noHp)
    {
        return Donatur::where('no_hp', $noHp)->first();
    }
}

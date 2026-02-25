<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class RamadhanPeriod extends Model
{
    protected $table = 'ramadhan_periods';

    protected $fillable = [
        'hijri_year',
        'label',
        'start_date',
        'end_date',
        'target',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'target'     => 'integer',
        'hijri_year' => 'integer',
    ];

    /**
     * Hitung jumlah hari Ramadhan (29 atau 30).
     */
    public function getTotalDaysAttribute(): int
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    /**
     * Konversi hari Ramadhan ke-N menjadi tanggal Masehi.
     * Hari ke-1 = start_date.
     */
    public function dayToDate(int $day): Carbon
    {
        return $this->start_date->copy()->addDays($day - 1);
    }

    /**
     * Tahun Masehi dari start_date, untuk shortcut.
     */
    public function getMasehiYearAttribute(): int
    {
        return $this->start_date->year;
    }

    /**
     * Scope: urutkan dari terlama ke terbaru.
     */
    public function scopeChronological($query)
    {
        return $query->orderBy('start_date', 'asc');
    }

    /**
     * Scope: urutkan dari terbaru ke terlama.
     */
    public function scopeLatestFirst($query)
    {
        return $query->orderBy('start_date', 'desc');
    }
}

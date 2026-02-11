<?php

namespace App\Http\Controllers;

use App\Models\AbsenCs;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AbsenCsController extends Controller
{
    public function create()
    {
        return view('absen.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_cs' => 'required|string|max:255',
            'status_kehadiran' => 'required',
            'tanggal' => 'required|date',
            'jam' => 'required',
            'tipe_absen' => 'required',
            'foto_base64' => 'required',
            'lokasi' => 'nullable|string',
            'keterangan' => 'nullable|string',
        ]);

        // Simpan foto dari base64 langsung ke folder public
        $image = $request->input('foto_base64');
        $image = str_replace('data:image/jpeg;base64,', '', $image);
        $image = str_replace(' ', '+', $image);
        $imageName = 'absen_' . Str::random(10) . '.jpg';
        file_put_contents(public_path("absen_foto/{$imageName}"), base64_decode($image));

        AbsenCs::create([
            'nama_cs' => $request->nama_cs,
            'status_kehadiran' => $request->status_kehadiran,
            'tanggal' => $request->tanggal,
            'jam' => $request->jam,
            'tipe_absen' => $request->tipe_absen,
            'foto' => "absen_foto/{$imageName}", // relatif ke public
            'lokasi' => $request->lokasi,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('absen.create')->with('success', 'Absen berhasil disimpan');
    }
}

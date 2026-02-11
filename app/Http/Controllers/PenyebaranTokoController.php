<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PenyebaranToko;
use Illuminate\Support\Facades\Storage;

class PenyebaranTokoController extends Controller
{
    public function create()
    {
        return view('penyebaran');
    }

    public function store(Request $request)
    {
        // Validasi data form
        $validated = $request->validate([
            'tanggal_registrasi' => 'required|date',
            'nama_cs' => 'required|string',
            'nomor_kencleng' => 'required|string',
            'nama_toko' => 'required|string',
            'nama_donatur' => 'required|string',
            'no_hp' => 'required|string',
            'alamat' => 'required|string',
            'latitude' => 'nullable|string',
            'longitude' => 'nullable|string',
            'keterangan' => 'nullable|string',
            'status' => 'nullable|string',
            'foto_base64' => 'nullable|string'
        ]);

       // Simpan foto dari base64 langsung ke folder public
        $image = $request->input('foto_base64');
        $image = str_replace('data:image/jpeg;base64,', '', $image);
        $image = str_replace(' ', '+', $image);
        $imageName = 'absen_' . Str::random(10) . '.jpg';
        file_put_contents(public_path("PenyebaranToko/{$imageName}"), base64_decode($image));

        // Simpan ke database
        PenyebaranToko::create($validated);

        return redirect()->back()->with('success', 'Data berhasil disimpan!');
    }
}

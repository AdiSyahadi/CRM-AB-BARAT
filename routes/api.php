<?php
use Illuminate\Support\Facades\DB;

Route::get('/laporans', function () {
    $laporans = DB::table('laporans')
        // ...select kolom...
        ->orderBy('tanggal', 'desc')
        ->get()
        ->map(function ($item) {
            return collect($item)->map(function ($value) {
                return $value ?? '';
            })->all();
        });

    return response()->json([
        'success' => true,
        'data' => $laporans,
        'total' => $laporans->count()
    ]);
});
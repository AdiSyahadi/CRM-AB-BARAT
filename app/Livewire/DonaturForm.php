<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Donatur; // Menggunakan model Donatur

class DonaturForm extends Component
{
    public $no_hp;
    public $nama_donatur, $jenis_kelamin, $email, $alamat, $sosmed_account, $program, $channel, $fundraiser, $kat_donatur, $did, $nama_cs;

    public function updatedNoHp($value)
    {
        // Cari data donatur berdasarkan nomor HP
        $donatur = Donatur::where('no_hp', $value)->first();

        if ($donatur) {
            // Jika data ditemukan, isi form secara otomatis
            $this->nama_donatur = $donatur->nama_donatur;
            $this->jenis_kelamin = $donatur->jenis_kelamin;
            $this->email = $donatur->email;
            $this->alamat = $donatur->alamat;
            $this->sosmed_account = $donatur->sosmed_account;
            $this->program = $donatur->program;
            $this->channel = $donatur->channel;
            $this->fundraiser = $donatur->fundraiser;
            $this->kat_donatur = $donatur->kat_donatur;
            $this->did = $donatur->did;
            $this->nama_cs = $donatur->nama_cs;
        } else {
            // Kosongkan field jika nomor HP tidak ditemukan
            $this->resetForm();
        }
    }

    public function resetForm()
    {
        $this->nama_donatur = '';
        $this->jenis_kelamin = '';
        $this->email = '';
        $this->alamat = '';
        $this->sosmed_account = '';
        $this->program = '';
        $this->channel = '';
        $this->fundraiser = '';
        $this->kat_donatur = '';
        $this->did = '';
        $this->nama_cs = '';
    }

    public function render()
    {
        return view('livewire.donatur-form');
    }
}

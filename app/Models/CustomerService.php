<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerService extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'team'];

    public function laporanPerolehans()
    {
        return $this->hasMany(LaporanPerolehan::class);
    }
}

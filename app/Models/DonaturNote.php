<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DonaturNote extends Model
{
    use HasFactory;

    protected $table = 'donatur_notes';

    protected $fillable = [
        'donatur_id',
        'user_id',
        'note',
    ];

    /**
     * Get the donatur that owns the note.
     */
    public function donatur()
    {
        return $this->belongsTo(Donatur::class, 'donatur_id');
    }

    /**
     * Get the user that created the note.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

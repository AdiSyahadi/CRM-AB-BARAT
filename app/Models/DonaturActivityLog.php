<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DonaturActivityLog extends Model
{
    use HasFactory;

    protected $table = 'donatur_activity_logs';
    
    public $timestamps = false; // Only has created_at

    protected $fillable = [
        'donatur_id',
        'user_id',
        'action',
        'description',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Get the donatur that owns the activity log.
     */
    public function donatur()
    {
        return $this->belongsTo(Donatur::class, 'donatur_id');
    }

    /**
     * Get the user that performed the action.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Static helper to log an activity
     */
    public static function log($donaturId, $action, $description = null, $metadata = null, $userId = null)
    {
        return self::create([
            'donatur_id' => $donaturId,
            'user_id' => $userId ?? auth()->id(),
            'action' => $action,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }
}

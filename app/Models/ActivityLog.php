<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
     // Jika kamu menggunakan nama tabel non-standar (bukan jamak dari nama model)
    protected $table = 'activity_logs';

    // Mass assignable fields
    protected $fillable = [
        'model_type',
        'model_id',
        'action',
        'changes',
        'causer_id',
    ];

    // Cast field changes menjadi array
    protected $casts = [
        'changes' => 'array',
    ];

    /**
     * Relasi ke user (siapa yang melakukan aksi)
     */
    public function causer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'causer_id');
    }

    /**
     * Relasi morph ke model yang dilog
     */
    public function subject()
    {
        return $this->morphTo(null, 'model_type', 'model_id');
    }
}

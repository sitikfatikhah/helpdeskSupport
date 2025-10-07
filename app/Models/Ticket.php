<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    use HasFactory;

    // public $timestamps = true;

    protected $fillable = [
    'user_id',
    'nik',
    'department_id',
    'ticket_number',
    'open_time',
    'close_time',
    'priority_level',
    'category',
    'description',
    'type_device',
    'operation_system',
    'software_or_application',
    'error_message',
    'step_taken',
    // 'attachment',
    'ticket_status',
    ];
    protected $casts = [
        // 'attachment' => 'array',
        'open_time' => 'datetime',
        'close_time' => 'datetime',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function user(): BelongsTo
    {
    return $this->belongsTo(User::class);
    }
    protected static function booted()
    {
        static::creating(function ($ticket) {
            if (empty($ticket->ticket_number)) {
                $yearSuffix = now()->format('y');
                $countThisYear = self::whereYear('created_at', now()->year)->count();
                $ticket->ticket_number = 'TKC-' . $yearSuffix . str_pad($countThisYear + 1, 3, '0', STR_PAD_LEFT);
            }
        });
    }



}

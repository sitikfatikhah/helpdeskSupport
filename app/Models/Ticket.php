<?php

namespace App\Models;

use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

    public static function generateTicketNumber()
{
    return DB::transaction(function () {
        $year = now()->format('y');
        $prefix = 'TKC-' . $year;

        // Gunakan SELECT ... FOR UPDATE agar aman dari race condition
        $last = self::whereYear('created_at', now()->year)
            ->lockForUpdate()
            ->orderByDesc('id')
            ->first();

        $next = $last ? intval(substr($last->ticket_number, -3)) + 1 : 1;
        $ticketNumber = sprintf('%s%03d', $prefix, $next);

        // // Simpan dummy ticket (opsional)
        // self::create([
        //     'ticket_number' => $ticketNumber,
        //     'ticket_status' => 'on_progress',
        //     'user_id' => Auth::id(),
        //     'open_time' => now(),
        //     'description' => 'text input',
        //     'category' => 'hardware',
        //     'priority_level' => 'low',
        // ]);

        return $ticketNumber;
    });
}


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
            $ticket->ticket_number = self::generateTicketNumber();
        }
    });
}

   }

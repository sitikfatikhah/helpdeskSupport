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

        // Kunci baris terakhir agar tidak terjadi race condition
        $last = self::where('ticket_number', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('id')
            ->first();

        // Ambil angka terakhir dari ticket_number
        if ($last) {
            // Ambil semua digit setelah prefix (misal TKC-25007 -> 7)
            $lastNumber = (int) str_replace($prefix, '', $last->ticket_number);
        } else {
            $lastNumber = 0;
        }

        $next = $lastNumber + 1;

        // Format nomor dengan 5 digit agar panjangnya konsisten
        $ticketNumber = sprintf('%s%05d', $prefix, $next);

        // Pastikan belum ada nomor duplikat (backup check)
        while (self::where('ticket_number', $ticketNumber)->exists()) {
            $next++;
            $ticketNumber = sprintf('%s%05d', $prefix, $next);
        }

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

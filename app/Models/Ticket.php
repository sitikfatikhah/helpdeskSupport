<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
    'user_id',
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
    'ticket_status',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;


class Asset extends Model
{
    protected $casts = [
        'os_name' => 'array',
    ];
    protected $table = 'assets';
    protected $fillable = [
        'device_id', //input device_id
        'type', //input type
        'status',
        'user_id',
        'department_id',
        'inventory_id',
        'ram',
        'serial_number',
        'os_name',
        'brand',
        'info',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
}

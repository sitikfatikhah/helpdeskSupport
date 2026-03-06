<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
    'department_name',
    'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function users() 
    {
    return $this->hasMany(User::class);
    }
    
    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }
    

}

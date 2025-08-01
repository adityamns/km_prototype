<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
      protected $fillable = [
        'user_id',
        'access_key',
        'name',
        'model',
        'temperature',
        'key',
        'description',
        'system_prompt',
        'filters',
        'is_active',
        'is_public',
        'is_internal',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'filters' => 'array',
        'is_active' => 'boolean',
        'is_public' => 'boolean',
        'is_internal' => 'boolean',
    ];
}

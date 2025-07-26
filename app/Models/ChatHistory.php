<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatHistory extends Model
{
    protected $fillable = [
        'chat_id', 'role', 'message', 'raw_response',
        'created_by', 'updated_by'
    ];

    protected $casts = [
        'raw_response' => 'array'
    ];
}


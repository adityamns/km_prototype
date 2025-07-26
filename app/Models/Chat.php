<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $fillable = [
        'user_id', 'agent_id', 'title',
        'created_by', 'updated_by'
    ];
}

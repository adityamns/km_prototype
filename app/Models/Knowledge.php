<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Pgvector\Laravel\HasVector;
class Knowledge extends Model
{
    protected $fillable = [
        'user_id', 'agent_id', 'text', 'embedding', 'source',
        'document_id', 'chunk_index', 'chunk_offset', 'metadata',
        'created_by', 'updated_by'
    ];

    protected $casts = [
        'embedding' => 'vector',
        'metadata' => 'array'
    ];
}

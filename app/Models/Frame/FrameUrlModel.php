<?php

namespace App\Models\Frame;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FrameUrlModel extends Model
{
    protected $connection = 'framework';
    protected $table = 'urls';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'masked_name',
        'parameters',
        'methods',
        'description',
        'input_description',
        'output_description',
        'controller_id',
        'scope_id',
        'created_by',
        'updated_by',
        'is_auth'
    ];
}

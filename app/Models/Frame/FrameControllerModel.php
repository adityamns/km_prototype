<?php

namespace App\Models\Frame;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FrameControllerModel extends Model
{
    protected $connection = 'framework';
    protected $table = 'controllers';
    protected $primaryKey = 'id';
    protected $fillable = [
        'namespaces',
        'application_id',
        'created_by',
        'updated_by'
    ];
}

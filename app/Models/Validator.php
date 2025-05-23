<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Validator extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'id',
        'user_id',
        'role',
        'name'
    ];
}

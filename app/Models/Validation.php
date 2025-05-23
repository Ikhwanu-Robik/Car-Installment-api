<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Validation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'validator_id',
        'society_id',
        'status',
        'job',
        'job_description',
        'income',
        'reason_accepted',
        'validator_notes'
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AvailableMonth extends Model
{
    protected $table = "available_month";

    protected $fillable = [
        'id',
        'installment_id',
        'month',
        'description',
        'nominal'
    ];

    protected $hidden = [
        'installment_id',
    ];
}

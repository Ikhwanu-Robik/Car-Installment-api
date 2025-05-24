<?php

namespace App\Models;

use App\Models\AvailableMonth;
use Illuminate\Database\Eloquent\Model;

class Installment extends Model
{
    protected $table = 'installment';

    protected $fillable = [
        'cars',
        'brand_id',
        'price',
        'description'
    ];

    public function availableMonth() {
        return $this->hasOne(AvailableMonth::class);
    }
}

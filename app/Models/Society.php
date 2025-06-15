<?php

namespace App\Models;

use App\Models\Regional;
use App\Models\Validation;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Society extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'societies';

    public $timestamps = false;

    protected $fillable = [
        'id_card_number',
        'password',
        'name',
        'born_date',
        'gender',
        'address',
        'regional_id',
    ];

    protected $hidden = [
        'password',
        'born_date',
        'gender',
        'address',
        'regional_id',
    ];

    public function regional()
    {
        return $this->belongsTo(Regional::class);
    }

    public function validation() {
        return $this->hasOne(Validation::class);
    }

    // protected $hidden = [
    //     'password',
    // ];
}

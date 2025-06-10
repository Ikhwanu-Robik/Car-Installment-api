<?php

namespace App\Models;

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

    protected function regional()
    {
        return $this->belongsTo(Regional::class);
    }

    // protected $hidden = [
    //     'password',
    // ];
}

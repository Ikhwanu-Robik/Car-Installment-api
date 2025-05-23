<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Society extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'societies';

    protected $fillable = [
        'id_card_number',
        'password',
        'name',
    ];

    protected function regional()
    {
        return $this->belongsTo(Regional::class);
    }

    // protected $hidden = [
    //     'password',
    // ];
}

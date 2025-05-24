<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstallmentApplySocieties extends Model
{
    protected $table = "installment_apply_societies";

    public $timestamps = false;

    protected $fillable = [
        "notes",
        "available_month_id",
        "date",
        "society_id",
        "installment_id"
    ];
}

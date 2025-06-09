<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstallmentApplySocieties extends Model
{
    protected $table = "installment_apply_societies";

    public $timestamps = false;

    protected $fillable = [
        "available_month_id",
        "date",
        "society_id",
        "installment_id",
        "apply_status",
        "notes",
    ];
    protected $hidden = [
        "id",
        "society_id",
        "date",
        "installment_id"
    ];

    public function society() {
        return $this->belongsTo(Society::class);
    }

    public function installment() {
        return $this->belongsTo(Installment::class);
    }

    public function availableMonth() {
        return $this->belongsTo(AvailableMonth::class);
    }
}

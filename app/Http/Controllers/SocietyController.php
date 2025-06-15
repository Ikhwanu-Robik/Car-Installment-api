<?php

namespace App\Http\Controllers;

use App\Models\Society;
use Illuminate\Http\Request;

class SocietyController extends Controller {
    public function findByIdCardNumber(Request $request, string $id_card_number) {
        $society = Society::with(["validation", "regional"])->where("id_card_number", "=", $id_card_number)->first();

        if (!$society) {
            return response()->json(["message" => "Societ not found"], 404);
        }

        return response()->json(["message" => "society found", "society" => $society]);
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Society;
use App\Models\Validation;
use App\Models\Installment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InstallmentController extends Controller
{
    public function getCars(Request $request)
    {
        $bearerToken = explode('|', $request->bearerToken());
        $tokenId = $bearerToken[0];
        $tokenUnhashed = $bearerToken[1];

        $personalAccessToken = DB::table('personal_access_tokens')->where('id', '=', $tokenId)->first();
        if (!hash_equals($personalAccessToken->token, hash('sha256', $tokenUnhashed))) {
            return response()->json(['message' => "Unauthorized user"], 401);
        }

        $society = Society::find($personalAccessToken->tokenable_id);
        $validation = Validation::where('society_id', '=', $society->id)->first();

        $installments = Installment::with('availableMonth')->where('price', '<', $validation->income)->join('brand', 'installment.brand_id', '=', 'brand.id')->get(['installment.id', 'cars AS car','brand.brand AS brand', 'description', 'price']);

        return response()->json(['cars' => $installments]);
    }
}

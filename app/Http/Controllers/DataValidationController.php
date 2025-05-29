<?php

namespace App\Http\Controllers;

use App\Models\Validation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class DataValidationController extends Controller
{
    public function requestValidation(Request $request)
    {
        $validated = $request->validate([
            'job' => 'required|string',
            'job_description' => 'required|string',
            'income' => 'required|numeric',
            'reason_accepted' => 'required|string'
        ]);

        $currentToken = $request->bearerToken();
        [$id, $token] = explode('|', $currentToken, 2);
        $tokenData = DB::table('personal_access_tokens')->find($id);
        $tokensMatch = (hash_equals($tokenData->token, hash('sha256', $token))) ? true : false;

        if (!$tokensMatch) {
            return response()->json(['message' => 'Unauthorized user'], 401);
        }

        $validated['society_id'] = $tokenData->tokenable_id;

        if (!Validation::where('society_id', '=', $validated['society_id'])->first()) {
            Validation::create($validated);
            Log::info("creating validaton");
        }

        return response()->json(['message' => "Request data validation sent successful"]);
    }

    public function getValidationStatus(Request $request)
    {
        $currentToken = $request->bearerToken();
        [$id, $token] = explode('|', $currentToken, 2);
        $tokenData = DB::table('personal_access_tokens')->find($id);
        $tokensMatch = (hash_equals($tokenData->token, hash('sha256', $token))) ? true : false;

        if (!$tokensMatch) {
            return response()->json(['message' => 'Unauthorized user'], 401);
        }

        $validations = Validation::with(['validator'])->where('society_id', '=', $tokenData->tokenable_id)->get();
        return response()->json($validations);
    }
}

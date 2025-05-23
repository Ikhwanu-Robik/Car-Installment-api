<?php

namespace App\Http\Controllers;

use App\Models\Validation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $currentToken = request()->bearerToken();
        [$id, $token] = explode('|', $currentToken, 2);
        $hashedToken = DB::table('personal_access_tokens')->find($id)->token;
        $tokensMatch = (hash_equals($hashedToken, hash('sha256', $token))) ? true : false;

        if (!$tokensMatch) {
            return response()->json(['message' => 'Unauthorized user'], 401);
        }

        $validated['society_id'] = $request->user()->id;

        Validation::create($validated);

        return response()->json(['message' => "Request data validation sent successful"]);
    }
}

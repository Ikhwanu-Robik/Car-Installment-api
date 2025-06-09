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
        if (!$tokenData) {
            return response()->json(['message' => "Unauthorized user"], 401);
        }
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
        if (!$tokenData) {
            return response()->json(['message' => "Unauthorized user"], 401);
        }
        $tokensMatch = (hash_equals($tokenData->token, hash('sha256', $token))) ? true : false;

        if (!$tokensMatch) {
            return response()->json(['message' => 'Unauthorized user'], 401);
        }

        $validations = Validation::with(['validator'])->where('society_id', '=', $tokenData->tokenable_id)->get();
        return response()->json($validations);
    }

    public function getValidationRequests(Request $request) {
        $validations = Validation::with('validator')->get();

        return response()->json(["validations" => $validations]);
    }

    public function setValidationStatus(Request $request, Validation $validation) {
        if ($validation->status == "accepted") {
            return response()->json(["message" => "The validation is already accepted and may not be changed anymore"], 422);
        }

        $validated = $request->validate([
            "notes" => 'required',
            "status" => "required",
        ]);

        $valid_status = ["accepted", "declined"];

        $isStatusValid = false;
        foreach ($valid_status as $stat) {
            if ($validated["status"] == $stat) {
                $isStatusValid = true;
                break;
            }
        }

        // if (!array_search($validated["status"], $valid_status)) {
        if (!$isStatusValid) {
            return response()->json(["message" => "the status must be either 'accepted' or 'declined'"], 422);
        }

        $validation->status = $validated["status"];
        $validation->validator_notes = $validated["notes"];
        $validation->validator_id = Auth::id();
        $validation->save();

        return $validation;
    }
}

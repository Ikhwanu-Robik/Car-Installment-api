<?php

namespace App\Http\Controllers;

use App\Models\Society;
use App\Models\Validation;
use App\Models\Installment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\InstallmentApplySocieties;
use App\Http\Requests\InstallmentApplicationRequest;

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

        $installments = Installment::with('availableMonth')->where('price', '<', $validation->income)->join('brand', 'installment.brand_id', '=', 'brand.id')->get(['installment.id', 'cars AS car', 'brand.brand AS brand', 'description', 'price']);

        return response()->json(['cars' => $installments]);
    }

    public function findCar(Request $request, $id)
    {
        $bearerToken = explode('|', $request->bearerToken());
        $tokenId = $bearerToken[0];
        $tokenUnhashed = $bearerToken[1];

        $personalAccessToken = DB::table('personal_access_tokens')->where('id', '=', $tokenId)->first();
        if (!hash_equals($personalAccessToken->token, hash('sha256', $tokenUnhashed))) {
            return response()->json(['message' => "Unauthorized user"], 401);
        }

        $installment = Installment::with('availableMonth')->where('installment.id', '=', $id)->join('brand', 'installment.brand_id', '=', 'brand.id')->get(['installment.id', 'cars AS car', 'brand.brand AS brand', 'description', 'price'])->first();

        return response()->json(['installment' => $installment]);
    }

    public function applyForInstallment(InstallmentApplicationRequest $request)
    {
        $validated = $request->validated();

        $bearerToken = explode('|', $request->bearerToken());
        $tokenId = $bearerToken[0];
        $tokenUnhashed = $bearerToken[1];

        $personalAccessToken = DB::table('personal_access_tokens')->where('id', '=', $tokenId)->first();
        if (!hash_equals($personalAccessToken->token, hash('sha256', $tokenUnhashed))) {
            return response()->json(['message' => "Unauthorized user"], 401);
        }

        $validation = Validation::where("society_id", "=", $personalAccessToken->tokenable_id)->first();
        if ($validation->status != "accepted") {
            return response()->json(["message" => "Your data validator must be accepted by validator before"], 401);
        }

        $validated["installment_id"] = $validated["instalment_id"];
        $validated["date"] = now()->format("Y-m-d");
        $validated["society_id"] = $personalAccessToken->tokenable_id;

        $installment = InstallmentApplySocieties::where("society_id", "=", $validated["society_id"])->where("installment_id", "=", $validated["instalment_id"])->where("available_month_id", "=", $validated["available_month_id"])->first();

        if ($installment) {
            return response()->json(["message" => " Application for a instalment can only be once"], 401);
        }

        InstallmentApplySocieties::create($validated);

        return response()->json(["message" => "Applying for Instalment successful"]);
    }
}
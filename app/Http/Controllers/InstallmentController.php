<?php

namespace App\Http\Controllers;

use App\Models\AvailableMonth;
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
        if (!$personalAccessToken) {
            return response()->json(['message' => "Unauthorized user"], 401);
        }
        if (!hash_equals($personalAccessToken->token, hash('sha256', $tokenUnhashed))) {
            return response()->json(['message' => "Unauthorized user"], 401);
        }

        $society = Society::find($personalAccessToken->tokenable_id);
        $validation = Validation::where('society_id', '=', $society->id)->first();

        $installments = Installment::with('availableMonth')->join('brand', 'installment.brand_id', '=', 'brand.id')->get(['installment.id', 'cars AS car', 'brand.brand AS brand', 'description', 'price']);

        return response()->json(['cars' => $installments]);
    }

    public function findCar(Request $request, $id)
    {
        $bearerToken = explode('|', $request->bearerToken());
        $tokenId = $bearerToken[0];
        $tokenUnhashed = $bearerToken[1];

        $personalAccessToken = DB::table('personal_access_tokens')->where('id', '=', $tokenId)->first();
        if (!$personalAccessToken) {
            return response()->json(['message' => "Unauthorized user"], 401);
        }
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
        if (!$personalAccessToken) {
            return response()->json(['message' => "Unauthorized user"], 401);
        }
        if (!hash_equals($personalAccessToken->token, hash('sha256', $tokenUnhashed))) {
            return response()->json(['message' => "Unauthorized user"], 401);
        }

        $validation = Validation::where("society_id", "=", $personalAccessToken->tokenable_id)->first();
        if ($validation->status != "accepted") {
            return response()->json(["message" => "Your data validator must be accepted by validator before"], 401);
        }

        $available_month = AvailableMonth::find($validated["available_month_id"]);
        if ($validation->income < $available_month->nominal) {
            return response()->json(["message" => "Your income must be equal to or greater than the instalment"]);
        }

        // TODO : Can apply for a instalment ONLY IF the income equal or exceed the calculation per month

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

    public function getInstallment(Request $request)
    {
        $bearerToken = explode('|', $request->bearerToken());
        $tokenId = $bearerToken[0];
        $tokenUnhashed = $bearerToken[1];

        $personalAccessToken = DB::table('personal_access_tokens')->where('id', '=', $tokenId)->first();
        if (!$personalAccessToken) {
            return response()->json(['message' => "Unauthorized user"], 401);
        }
        if (!hash_equals($personalAccessToken->token, hash('sha256', $tokenUnhashed))) {
            return response()->json(['message' => "Unauthorized user"], 401);
        }

        $installments = Installment::with("applications")->where('installment_apply_societies.society_id', '=', $personalAccessToken->tokenable_id)->join('brand', 'installment.brand_id', '=', 'brand.id')->join("installment_apply_societies", "installment.id", "installment_apply_societies.installment_id")->get(['installment.id', 'cars AS car', 'brand.brand AS brand', 'price', 'description']);
        $available_month = AvailableMonth::all();

        $installment_month_id = [];
        foreach ($installments as $installment) {
            foreach ($installment->applications as $application) {
                $month_model = $available_month->find($application->available_month_id);

                $application["month"] = $month_model->month;
                $application["nominal"] = $month_model->nominal;

                unset($application["available_month_id"]);
            }
        }

        return response()->json([
            "installments" => $installments
        ]);
    }

    public function getInstallmentApplicationRequests(Request $request)
    {
        $installments = DB::table("installment_apply_societies")
        ->join("societies", "installment_apply_societies.society_id", "=", "societies.id")
        ->join("installment", "installment_apply_societies.installment_id", "=", "installment.id")
        ->join("available_month", "installment_apply_societies.available_month_id", "=", "available_month.id")
        ->get(["installment_apply_societies.id", "date", "apply_status", "notes", "id_card_number", "name", "cars AS car_name", "price", "month", "nominal"]);

        return response()->json(["installment_applications" => $installments]);
    }

    public function setInstallmentApplicationStatus(Request $request, InstallmentApplySocieties $installment) {
        $validated = $request->validate(["status" => "required"]);

        $valid_status = ["accepted", "rejected"];

        $isStatusValid = false;
        foreach ($valid_status as $stat) {
            if ($validated["status"] == $stat) {
                $isStatusValid = true;
                break;
            }
        }

        if (!$isStatusValid) {
            return response()->json(["message" => "the status must be either 'accepted' or 'rejected'"], 422);
        }

        $installment->apply_status = $validated["status"];
        $installment->save();

        return response()->json(["message" => "installment application status changed"]);
    }
}
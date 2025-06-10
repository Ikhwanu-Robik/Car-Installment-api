<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Society;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request) {
        $validated = $request->validate([
            "id_card_number" => "required|unique:societies,id_card_number",
            "password" => "required",
            "name" => "required",
            "born_date" => "required|date",
            "gender" => "required",
            "address" => "required",
            "regional_id" => "required|exists:regionals,id",
        ]);

        $valid_genders = ["male", "female"];

        $isGenderValid = false;
        foreach($valid_genders as $gender) {
            if ($gender == $validated["gender"]) {
                $isGenderValid = true;
                break;
            }
        }

        if (!$isGenderValid) {
            return response()->json(["message" => "gender must be either 'male' or 'female'"]);
        }

        $validated["password"] = Hash::make($validated["password"]);

        $society = Society::create($validated);

        return response()->json(["message" => "registration successful", "society" => $society]);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'username' => "sometimes",
            'id_card_number' => 'sometimes|numeric',
            'password' => 'required'
        ]);

        $isUser = isset($validated["username"]) ? true : false;
        $isSociety = isset($validated["id_card_number"]) ? true : false;

        $credentials = [];

        if ($isUser) {
            $credentials = [
                "username" => $validated["username"],
                "password" => $validated["password"]
            ];
        } else if ($isSociety) {
            $credentials = [
                "id_card_number" => $validated["id_card_number"],
                "password" => $validated["password"]
            ];
        }

        if ($isUser && Auth::guard('web')->attempt($credentials)) {
            $user = Auth::user()->with('validator')->first();

            return response()->json(["message" => "Logged in as validator", "user" => $user, "token" => $user->createToken("GGE")->plainTextToken]);
        }
        
        if ($isSociety && Auth::guard('society')->attempt($credentials)) {
            $society = Society::where("id_card_number", "=", $credentials["id_card_number"])->first();
            $token = $society->createToken("EGG")->plainTextToken;
            $data = [
                'name' => $society->name,
                'born_date' => $society->born_date,
                'gender' => $society->gender,
                'address' => $society->address,
                'token' => $token,
                'regional' => $society->regional
            ];

            return response()->json($data);
        }

        if ($isUser) {
            return response()->json(["message" => "username or password incorrect"], 401);   
        } else if ($isSociety) {
            return response()->json(["message" => "ID Card Number or Password incorrect"], 401);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout success']);
    }
}

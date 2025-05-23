<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Society;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validated = $request->validate([
            'id_card_number' => 'required|numeric',
            'password' => 'required'
        ]);

        if (Auth::attempt($validated)) {
            $society = Society::where('id_card_number', '=', $validated['id_card_number'])->first();
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
        return response()->json([
            'message' => "ID Card Number or Password incorrect",
        ], 401);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout success']);
    }
}

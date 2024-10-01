<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {

        $request->validate([
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:8|max:255'
        ]);


        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'error' => 'The Provided Credential are incorrect'
            ], status: 401);
        }

        $token = $user->createToken($user->name . 'Auth-Token')->plainTextToken;

        return response()->json([
            'meassege' => 'Login succesfully',
            'token_type' => 'Bearer',
            'token' => $token,
        ], status: 200);
    }

    public function register(Request $request): JsonResponse
    {

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'password' => 'required|string|min:8|max:255'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email'  => $request->email,
            'password' => Hash::make($request->password)
        ]);

        if ($user){
            $token = $user->createToken($user->name . 'Auth-Token')->plainTextToken;

        return response()->json([
            'meassege' => 'Register succesfully',
            'token_type' => 'Bearer',
            'token' => $token,
        ], status: 201);
        }
        else{

            return response()->json([
                'meassege' => 'Something Went Wrong !!'
            ], status: 500);
        }
    }
}

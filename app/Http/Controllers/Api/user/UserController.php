<?php

namespace App\Http\Controllers\API\user;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserDetails;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

  // Retrieve all users with their details
  public function index()
  {
    $users = User::with(['details', 'batch'])->get();
    return response()->json($users);
  }

  // login 
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


  public function checkUsername(Request $request)
  {
      $username = $request->query('username');
  
      // Logic to check if the username is available
      $isAvailable = !User::where('username', $username)->exists();
  
      return response()->json(['available' => $isAvailable]);
  }
  

  // Store a new user with details
  public function register(Request $request): JsonResponse
  {
    // Validate basic user info
    $validator = Validator::make($request->all(), [
      'first_name' => 'required|string|max:255',
      'last_name' => 'required|string|max:255',
      'username' => 'required|string|unique:users,username|max:255',
      'email' => 'required|string|email|unique:users,email|max:255',
      'birthdate' => 'nullable|date',
      'phone' => 'nullable|string|max:11',
      'password' => 'required|string|min:8|max:255'
      // You can add more validations if necessary
    ]);

    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }

    // Create the user
    $user = User::create([
      'first_name' => $request->first_name,
      'last_name' => $request->last_name,
      'username' => $request->username,
      'email' => $request->email,
      'phone' => $request->phone,
      'profile_picture' => $request->profile_picture,
      'date_of_birth' => $request->birthdate,
      'password' => Hash::make($request->password)
    ]);

    // Store detailed user information
    $user->details()->create([
      'last_login_at' => $request->last_login_at,
      'devices' => $request->devices,
      'browsing_activity' => $request->browsing_activity,
      'blocked_users' => $request->blocked_users,
    ]);

    // Store user batch information
    $user->batch()->create([
      'department' => $request->department,
      'semister' => $request->semister,
      'bteb_roll' => $request->btebroll,
      'session' => $request->session,
    ]);
    if ($user) {
      $token = $user->createToken($user->name . 'Auth-Token')->plainTextToken;

      return response()->json([
        $user->load('details', 'batch'),
        'message' => 'Registered successfully',
        'token_type' => 'Bearer',
        'token' => $token,
      ], 201);
    } else {
      return response()->json([
        'message' => 'Something went wrong!!'
      ], 500);
    }


    // return response()->json($user->load('details', 'batch'), 201);
  }

  // In your UserController.php



}

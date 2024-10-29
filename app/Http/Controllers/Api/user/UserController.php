<?php

namespace App\Http\Controllers\API\user;

use App\Models\User;
use App\Models\UserDetails;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Batch_details;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

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
    // Validate the request
    $request->validate([
      'credential' => 'required|string|max:255', // Handle both email and username
      'password' => 'required|string|min:8|max:255',
    ]);

    // Attempt to find the user by email or username
    $user = User::where('email', $request->credential)
      ->orWhere('username', $request->credential)
      ->first();

    if (!$user) {
      if (filter_var($request->credential, FILTER_VALIDATE_EMAIL)) {
        return response()->json([
          'error' => 'No account found with this email.'
        ], 404);
      } else {
        return response()->json([
          'error' => 'No account found with this username.'
        ], 404);
      }
    }

    // Validate user credentials
    if (!Hash::check($request->password, $user->password)) {
      return response()->json([
        'error' => 'The provided password is incorrect.'
      ], 401);
    }

    $token = $user->createToken($user->name . ' Auth-Token')->plainTextToken;

    return response()->json([
      'message' => 'Login successfully',
      'token_type' => 'Bearer',
      'token' => $token,
    ], 200);
  }



  public function checkEnquiry(Request $request)
  {
    $username = $request->query('username');
    $useremail = $request->query('email');
    $userRoll = $request->query('btebroll');

    // Check if the username, email, or roll exists
    $isAvailable = !User::where('username', $username)->exists(); // Check if username exists
    $emailAvailable = !User::where('email', $useremail)->exists(); // Check if email exists
    $rollAvailable = !Batch_details::where('bteb_roll', $userRoll)->exists(); // Check if roll exists

    // Return the response with the results
    return response()->json([
      'available' => $isAvailable,
      'availableEmail' => $emailAvailable,
      'availableRoll' => $rollAvailable,
    ]);
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
      $this->sendOtp(new Request(['email' => $user->email]));
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
  }
  public function sendOtp(Request $request)
  {
    $request->validate(['email' => 'required|email|exists:users,email']);

    $otp = random_int(100000, 999999);
    $expiresAt = now()->addMinutes(5);

    $user = User::where('email', $request->email)->first();
    $user->otp = $otp;
    $user->otp_expires_at = $expiresAt;
    $user->save();

    Mail::to($user->email)->send(new \App\Mail\OtpMail($otp));

    return response()->json(['message' => 'OTP sent to your email.']);
  }



  public function verifyOtp(Request $request)
  {
      $request->validate([
          'otp' => 'required|digits:6',
      ]);
  
      // Get email from the session
      // $email = session('email');
  
      // if (!$email) {
      //     return response()->json(['message' => 'User not found.'], 404);
      // }
  
      $user = User::where('email', 'ruhebalaj@mailinator.com')->first();
  
      if ($user && $user->otp === $request->otp && now()->isBefore($user->otp_expires_at)) {
          $user->otp = null;
          $user->otp_expires_at = null;
          $user->save();
  
          return response()->json(['message' => 'OTP verified successfully.'], 200);
      }
  
      return response()->json(['message' => 'Invalid or expired OTP.'], 400);
  }
  


  public function logout(Request $request)
  {
    // Check if the user is authenticated
    if (Auth::check()) {
      // Revoke the user's token
      $request->user()->tokens()->delete(); // This will revoke all tokens

      return response()->json([
        'status' => 'success',
        'message' => 'Successfully logged out.'
      ], 200);
    }

    return response()->json([
      'status' => 'error',
      'message' => 'User not authenticated.'
    ], 401); // Unauthorized response if user is not authenticated
  }

  // In your UserController.php



}

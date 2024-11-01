<?php

namespace App\Http\Controllers\API\user;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Batch_details;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
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
  public function login(Request $request)
  {
    $request->merge([
      'remember' => filter_var($request->remember, FILTER_VALIDATE_BOOLEAN)
    ]);
    // Validate the request
    $request->validate([
      'credential' => 'required|string|max:255', // Handle both email and username
      'password' => 'required|string|min:8|max:255',
      'remember' => 'boolean',
    ]);

    // Attempt to find the user by email or username
    $user = User::where('email', $request->credential)
      ->orWhere('username', $request->credential)
      ->first();

    if (!$user) {
      $errorMessage = filter_var($request->credential, FILTER_VALIDATE_EMAIL) ?
        'No account found with this email.' : 'No account found with this username.';
      return response()->json(['error' => $errorMessage], 404);
    }

    // Validate user credentials
    if (!Hash::check($request->password, $user->password)) {
      return response()->json(['error' => 'The provided password is incorrect.'], 401);
    }

    // Check if the user's email is verified
    if (is_null($user->email_verified_at)) {
      // Generate OTP
      $otp = random_int(100000, 999999);
      $expiresAt = now()->addMinutes(5);

      // Save OTP and expiration time to the user's record
      $user->otp = $otp;
      $user->otp_expires_at = $expiresAt;
      $user->save();

      // Send OTP to the user's email
      Mail::to($user->email)->send(new \App\Mail\OtpMail($otp));

      // Instead of session, return the email and OTP expiration in the response
      return response()->json([
        'message' => 'An OTP has been sent to your email for verification.',
        'email' => $user->email, // Send the email back in the response
        'otp_expires_at' => $expiresAt, // Include OTP expiration time
      ], 403);
    }

    // Email is verified, proceed with login
    $token = $user->createToken($user->name . ' Auth-Token')->plainTextToken;

    // If 'remember' is true, save token to remember_token field and set cookie
    if ($request->remember) {
      $user->remember_token = $token;
      $user->save();

      return response()->json(['message' => 'Login successful', 'token' => $token])
        ->cookie('remember_token', $token, 60 * 24 * 30); // 30 days expiration
    }
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
      'password' => Hash::make($request->password),
      'email_verified_at' => null,  // Ensure email verification starts as null
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
        'user' => $user->load('details', 'batch'),
        'message' => 'Registered successfully. OTP sent for verification.',
        'token_type' => 'Bearer',
        'token' => $token,
      ], 201);
    } else {
      return response()->json(['message' => 'Something went wrong!!'], 500);
    }
  }

  // Send OTP email
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

  // Verify OTP and confirm email
  public function verifyOtp(Request $request)
  {
    $request->validate([
      'email' => 'required|email|exists:users,email',
      'otp' => 'required|digits:6',
    ]);

    $user = User::where('email', $request->email)->first();

    if ($user && $user->otp === $request->otp && now()->isBefore($user->otp_expires_at)) {
      // Clear OTP and set email_verified_at timestamp
      $user->otp = null;
      $user->otp_expires_at = null;
      $user->email_verified_at = now();
      $user->save();

      return response()->json(['message' => 'OTP verified and email confirmed successfully.'], 200);
    }

    return response()->json(['message' => 'Invalid or expired OTP.'], 400);
  }



  public function logout(Request $request)
  {
    // Check if the user is authenticated
    if (Auth::check()) {
      $user = $request->user();

      // Revoke the user's tokens
      $user->tokens()->delete(); // This will revoke all tokens

      // Clear the remember_token from the user's record
      $user->remember_token = null;
      $user->save();

      // Clear the remember_token cookie
      return response()->json([
        'status' => 'success',
        'message' => 'Successfully logged out.'
      ], 200)->withCookie(cookie()->forget('remember_token'));
    }

    // Return an error if the user is not authenticated
    return response()->json([
      'status' => 'error',
      'message' => 'User not authenticated.'
    ], 401);
  }


  // In your UserController.php

  // <-########################## Forgot Password ###########################->

  public function sendOtpForgot(Request $request)
  {
    // Validate the incoming request
    $request->validate(['email' => 'required|email']);

    $user = User::where('email', $request->email)->first();
    // Check if the user exists
    if (!$user) {
      return response()->json(['message' => 'User not found.'], 404);
    }

    // session(['otp' => $otp, 'otp_email' => $user->email]);

    // Generate a random OTP
    $otp = random_int(100000, 999999);
    $expiresAt = now()->addMinutes(5);
    $user->otp = $otp;
    $user->otp_expires_at = $expiresAt;
    $user->save();
    // Send the OTP via email
    try {
      Mail::to($user->email)->send(new \App\Mail\ForgotPasswordOtpMail($otp));
    } catch (\Exception $e) {
      return response()->json(['message' => 'Failed to send OTP. Please try again.'], 500);
    }

    // Return success response
    return response()->json(['message' => 'Please check your email for the OTP to complete the process.']);
  }

  public function verifyOtpForgot(Request $request)
  {
      // Validate the incoming request
      $request->validate([
        'email' => 'required|email|exists:users,email',
        'otp' => 'required|digits:6',
      ]);
  
      $user = User::where('email', $request->email)->first();
  
      if ($user && $user->otp === $request->otp && now()->isBefore($user->otp_expires_at)) {
        // Clear OTP and set email_verified_at timestamp
        $user->otp = null;
        $user->otp_expires_at = null;
        // $user->email_verified_at = now();
        $user->save();
  
        return response()->json(['message' => 'OTP verified successfully.'], 200);
      }
  
      return response()->json(['message' => 'Invalid or expired OTP.'], 400);
  }
  


  public function resetPassword(Request $request)
  {
    $request->validate([
      'email' => 'required|email',
      'password' => 'required|confirmed|min:6',
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user) return response()->json(['message' => 'User not found.'], 404);

    $user->password = bcrypt($request->password);
    $user->save();

    return response()->json(['message' => 'Password reset successful! Securely logging you in']);
  }
}

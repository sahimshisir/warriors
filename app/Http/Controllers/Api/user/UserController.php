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
  // Store a new user with details
  public function register(Request $request)
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


    return response()->json($user->load('details', 'batch'), 201);
  }

  // Retrieve all users with their details
  public function index()
  {
    $users = User::with(['details', 'batch'])->get();
    return response()->json($users);
  }

  // Retrieve a specific user with 

  //   public function show($id)
  //   {
  //       $user = User::with('details')->findOrFail($id);
  //       return response()->json($user);
  //   }

  // Update a user and their details

  //   public function update(Request $request, $id)
  //   {
  //       $user = User::findOrFail($id);

  //       // Validate and update basic user info
  //       $user->update($request->only('name', 'username', 'email', 'phone', 'date_of_birth'));

  //       // Update detailed user info
  //       $user->details->update($request->except('name', 'username', 'email', 'phone', 'date_of_birth'));

  //       return response()->json($user->load('details'));
  //   }

  // Delete a user and their details

  //   public function destroy($id)
  //   {
  //       $user = User::findOrFail($id);
  //       $user->delete();
  //       return response()->json(null, 204);
  //   }
}

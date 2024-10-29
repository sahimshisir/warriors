<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class RememberToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            // Check for remember token in the request
            $rememberToken = $request->cookie('remember_token'); // Retrieve the token from cookies
        
            if ($rememberToken) {
                // Find the user based on the remember token
                $user = User::where('remember_token', $rememberToken)->first();
        
                if ($user) {
                    // Automatically log in the user
                    Auth::login($user);
                }
            }
        }
        return $next($request);
    }
}

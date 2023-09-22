<?php

namespace App\Http\Middleware;
use App\Models\User;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{

    /** 
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {

            $user = Auth::user();

            if ($user->user_level === 1) {

                return $next($request);
            }

        }

        // If the user is not an admin, you can redirect or return an error response.
        return response()->json(['message' => 'Unauthorized. Only admins can access this resource.'], 403);
    
    }
}

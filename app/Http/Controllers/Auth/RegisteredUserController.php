<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivitiesLog;
use App\Models\User;
use App\Notifications\TwoFactorCodeNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use Stevebauman\Location\Facades\Location;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): Response | JsonResponse
    {
        $request->validate([
            'firstName' => ['required', 'string', 'max:255'],
            'lastName' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'user_level' => ['nullable', 'integer'],
        ]);

        $user_level = 0;
        if($request->user_level && Auth::user()->user_level === 1) {
            $user_level = $request->user_level;
        }

        $user = User::create([
            'firstName' => $request->firstName,
            'lastName' => $request->lastName,
            'country' => $request->country,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_level' => $user_level
        ]);

        event(new Registered($user));

        $user = User::findOrFail($user->id);
        
        $location = Location::get($request->ip());
    
        $activitiesLog = new ActivitiesLog([
            'user_ip' => $request->ip(),
            'action' => 'REGISTER',
            'country' => $location->countryName,
            'city' => $location->cityName,
        ]);

        $user->activitiesLogs()->save($activitiesLog);
        

        if (Auth::check() && Auth::user()->user_level === 1) {
            $user->load(['privateKey', 'balance.wallet']);
            
            return response()->json(['message' => 'new user registered', 'user' => $user], 200);

        } else {
            
            $code = rand(10000, 99999);

            $user->notify(new TwoFactorCodeNotification($code));

            $request->session()->put('authenticated_user_id', $user->id);
            $request->session()->put('two_factor_code', $code);

            $request->session()->regenerate();  

            return response()->json(['message' => "An OTP code was send to $request->email", 'email' => $request->email], 200);
            
        }



        
    }
}

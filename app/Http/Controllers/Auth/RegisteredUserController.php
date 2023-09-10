<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use Illuminate\Http\JsonResponse;

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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'user_level' => ['nullable', 'integer']
        ]);

        if ($request->user_level === '1' && auth()->user()->user_level !== 1) {
            return response()->json(['message' => 'only admin can assign admin.'], 403);
        }

        Log::info('if test', [
            $request->user_level === '1',
            auth()->user()->user_level !== 1
        ]);


        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_level' => $request->user_level
        ]);

        event(new Registered($user));

        $role = $user->user_level === 1 ? 'admin' : 'user';

        if(!auth()->check()) {
            Auth::login($user);
            return response()->noContent();
        } else {
            return response()->json(['message' => "New $role $user->name registered."], 200);
        }



    }
}

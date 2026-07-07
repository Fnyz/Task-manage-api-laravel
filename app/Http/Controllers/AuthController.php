<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\StoreNewUserRequest;
use App\Http\Requests\LoginRequest;
use Illuminate\Auth\Events\Registered;

class AuthController extends Controller
{
    public function register(StoreNewUserRequest $request)
    {
        // Create a new user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
        ]);

        // Send email verification notification
        event(new Registered($user));

        return response()->json(['message' => 'User registered successfully', 'user' => $user], 201);
    }

    public function login(LoginRequest $request){

        // check if the user exists
        $user = User::where('email', $request->email)->first();

        // check if the user exists and the password is correct
        if(!$user || !Hash::check($request->password, $user->password)){
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // create a token for the user
        $token = $user->createToken('api_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token
        ]);
    }

    public function logout(Request $request){
        // Revoke the token that was used to authenticate the current request
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }
}

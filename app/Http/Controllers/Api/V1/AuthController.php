<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $attempt = Auth::guard('web')->attempt($validated);

        if($attempt) {
            $user = User::where('email', $request->email)->first();
            $token = $user->createToken($user->id)->plainTextToken;

            return response()->json(['token' => $token], 200);
        }
        else {
            return response()->json(['message' => 'User credentials are not correct.'], 422);
        }
    }
}

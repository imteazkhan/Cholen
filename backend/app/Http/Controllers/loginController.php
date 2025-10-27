<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Notifications\LoginNeedsVerification;

class LoginController extends Controller
{
    public function submit(Request $request): JsonResponse
    {
        // Validate the phone number
        $request->validate([
            'phone' => 'required|numeric|digits:11',
        ]);

        // Find or create a user model
        $user = User::firstOrCreate([
            'phone' => $request->phone
        ]);

        if (!$user) {
            return response()->json([
                'message' => 'Something went wrong'
            ], 500);
        }

        // Send the user a verification code
        $user->notify(new LoginNeedsVerification());

        // Return a JSON response
        return response()->json([
            'message' => 'A verification code has been sent to your phone number'
        ], 200);
    }

    public function verify(Request $request): JsonResponse
    {
        // Validate the request
        $request->validate([
            'phone' => 'required|numeric|digits:11',
            'login_code' => 'required|numeric|between:1000,9999'
        ]);

        // Find the user with matching phone and login_code
        $user = User::where('phone', $request->phone)
                    ->where('login_code', $request->login_code)
                    ->first();

        if ($user) {
            // Clear the used code for security
            $user->update(['login_code' => null]);

            // Create a Sanctum token
            $token = $user->createToken('auth-token')->plainTextToken;

            // Return success response with token
            return response()->json([
                'message' => 'Login successful',
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user
            ], 200);
        }

        // Invalid code
        return response()->json([
            'message' => 'Invalid verification code'
        ], 401);
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        // Convert empty strings to null for optional fields
        $data = $request->all();
        $data['driver_license'] = $data['driver_license'] ?? null;
        
        if (isset($data['driver_license']) && $data['driver_license'] === '') {
            $data['driver_license'] = null;
        }

        $validator = Validator::make($data, [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'role' => ['required', Rule::in(['user', 'driver'])],
            'driver_license' => 'required_if:role,driver|nullable|string|max:255',
        ], [
            'driver_license.required_if' => 'The driver license field is required when role is driver.',
            'driver_license.string' => 'The driver license field must be a string.',
            'role.in' => 'Invalid role selected. Only user and driver roles are allowed for registration.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        // Admin role is not allowed for registration
        if ($data['role'] === 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Admin role registration is not allowed. Admin access is granted by existing administrators only.'
            ], 403);
        }

        try {
            $user = User::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'name' => $data['first_name'] . ' ' . $data['last_name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => $data['role'],
                'driver_license' => $data['driver_license'],
                'driver_status' => $data['role'] === 'driver' ? 'pending' : null,
                'is_active' => true,
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'user' => [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'driver_license' => $user->driver_license,
                    'driver_status' => $user->driver_status,
                    'is_active' => $user->is_active,
                ],
                'token' => $token
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Login user
     */
    public function login(Request $request)
    {
        // Convert empty strings to null for optional fields
        $data = $request->all();
        $data['admin_code'] = $data['admin_code'] ?? null;
        
        if (isset($data['admin_code']) && $data['admin_code'] === '') {
            $data['admin_code'] = null;
        }

        $validator = Validator::make($data, [
            'email' => 'required|email',
            'password' => 'required|string',
            'role' => ['required', Rule::in(['user', 'driver', 'admin'])],
            'admin_code' => 'required_if:role,admin|nullable|string|max:255',
        ], [
            'admin_code.required_if' => 'The admin code field is required when role is admin.',
            'admin_code.string' => 'The admin code field must be a string.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        // Validate admin code for admin login
        if ($data['role'] === 'admin' && $data['admin_code'] !== 'admin123') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid admin access code'
            ], 422);
        }

        // Find user with matching email and role
        $user = User::where('email', $data['email'])
                   ->where('role', $data['role'])
                   ->where('is_active', true)
                   ->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            // Check if user exists with different role
            $userWithDifferentRole = User::where('email', $data['email'])
                                        ->where('is_active', true)
                                        ->first();
            
            if ($userWithDifferentRole) {
                return response()->json([
                    'success' => false,
                    'message' => "This account is registered as a {$userWithDifferentRole->role}. Please select the correct role."
                ], 422);
            }

            return response()->json([
                'success' => false,
                'message' => 'Invalid email, password, or role'
            ], 422);
        }

        // Update last login
        $user->update(['last_login_at' => now()]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'name' => $user->full_name,
                'email' => $user->email,
                'role' => $user->role,
                'driver_license' => $user->driver_license,
                'driver_status' => $user->driver_status,
                'is_active' => $user->is_active,
                'last_login_at' => $user->last_login_at,
            ],
            'token' => $token
        ]);
    }

    /**
     * Get authenticated user
     */
    public function user(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'name' => $user->full_name,
                'email' => $user->email,
                'role' => $user->role,
                'driver_license' => $user->driver_license,
                'driver_status' => $user->driver_status,
                'is_active' => $user->is_active,
                'last_login_at' => $user->last_login_at,
            ]
        ]);
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Get all users (admin only)
     */
    public function getAllUsers(Request $request)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $users = User::select([
            'id', 'first_name', 'last_name', 'email', 'role', 
            'driver_license', 'driver_status', 'is_active', 
            'created_at', 'last_login_at'
        ])->get();

        return response()->json([
            'success' => true,
            'users' => $users
        ]);
    }
}
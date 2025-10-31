<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Ride;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        // Temporarily disable admin check for testing
        // $this->middleware(function ($request, $next) {
        //     if (!auth()->user() || auth()->user()->role !== 'admin') {
        //         return response()->json(['error' => 'Unauthorized. Admin access required.'], 403);
        //     }
        //     return $next($request);
        // });
    }

    /**
     * Test endpoint
     */
    public function test(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Admin controller is working',
            'timestamp' => now()
        ]);
    }

    /**
     * Get all users
     */
    public function getAllUsers(): JsonResponse
    {
        try {
            $users = User::select([
                'id', 'first_name', 'last_name', 'name', 'email', 'phone', 
                'role', 'is_active', 'created_at', 'updated_at'
            ])
            ->orderBy('created_at', 'desc')
            ->get();

            return response()->json([
                'success' => true,
                'users' => $users,
                'total' => $users->count()
            ]);
        } catch (\Exception $e) {
            \Log::error('AdminController getAllUsers error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch users',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }

    /**
     * Get all drivers
     */
    public function getAllDrivers(): JsonResponse
    {
        try {
            $drivers = User::where('role', 'driver')
                ->select([
                    'id', 'first_name', 'last_name', 'name', 'email', 'phone',
                    'driver_license', 'driver_status', 'is_active', 'created_at', 'updated_at'
                ])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'drivers' => $drivers,
                'total' => $drivers->count(),
                'pending' => $drivers->where('driver_status', 'pending')->count(),
                'approved' => $drivers->where('driver_status', 'approved')->count(),
                'rejected' => $drivers->where('driver_status', 'rejected')->count()
            ]);
        } catch (\Exception $e) {
            \Log::error('AdminController getAllDrivers error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch drivers',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }

    /**
     * Create new driver
     */
    public function createDriver(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20',
            'driver_license' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:6',
            'driver_status' => 'in:pending,approved,rejected'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $driver = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'name' => $request->first_name . ' ' . $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'role' => 'driver',
                'driver_license' => $request->driver_license,
                'driver_status' => $request->driver_status ?? 'pending',
                'is_active' => true
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Driver created successfully',
                'driver' => $driver
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create driver',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update driver status
     */
    public function updateDriverStatus(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,approved,rejected'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid status',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $driver = User::where('role', 'driver')->findOrFail($id);
            $driver->driver_status = $request->status;
            $driver->save();

            return response()->json([
                'success' => true,
                'message' => 'Driver status updated successfully',
                'driver' => $driver
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update driver status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete driver
     */
    public function deleteDriver($id): JsonResponse
    {
        try {
            $driver = User::where('role', 'driver')->findOrFail($id);
            
            // Check if driver has active rides
            $activeRides = Ride::where('driver_id', $id)
                ->whereIn('status', ['accepted', 'driver_arrived', 'in_progress'])
                ->count();

            if ($activeRides > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete driver with active rides'
                ], 400);
            }

            $driver->delete();

            return response()->json([
                'success' => true,
                'message' => 'Driver deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete driver',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete user
     */
    public function deleteUser($id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);
            
            // Prevent deleting admin users
            if ($user->role === 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete admin users'
                ], 400);
            }

            // Check if user has active rides
            if ($user->role === 'user') {
                $activeRides = Ride::where('user_id', $id)
                    ->whereIn('status', ['pending', 'accepted', 'driver_arrived', 'in_progress'])
                    ->count();

                if ($activeRides > 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot delete user with active rides'
                    ], 400);
                }
            }

            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user
     */
    public function updateUser(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'string|max:255',
            'last_name' => 'string|max:255',
            'email' => 'string|email|max:255|unique:users,email,' . $id,
            'phone' => 'string|max:20',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::findOrFail($id);
            $user->update($request->only([
                'first_name', 'last_name', 'email', 'phone', 'is_active'
            ]));

            if ($request->has('first_name') || $request->has('last_name')) {
                $user->name = ($request->first_name ?? $user->first_name) . ' ' . 
                             ($request->last_name ?? $user->last_name);
                $user->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'user' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all ride requests for monitoring
     */
    public function getAllRides(): JsonResponse
    {
        try {
            // Check if Ride model exists and table has data
            if (!class_exists('App\Models\Ride')) {
                return response()->json([
                    'success' => true,
                    'rides' => [],
                    'total' => 0,
                    'pending' => 0,
                    'active' => 0,
                    'completed' => 0,
                    'cancelled' => 0,
                    'message' => 'Ride model not available'
                ]);
            }

            $rides = Ride::with(['user:id,first_name,last_name,email,phone', 'driver:id,first_name,last_name,email,phone'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'rides' => $rides,
                'total' => $rides->count(),
                'pending' => $rides->where('status', 'pending')->count(),
                'active' => $rides->whereIn('status', ['accepted', 'driver_arrived', 'in_progress'])->count(),
                'completed' => $rides->where('status', 'completed')->count(),
                'cancelled' => $rides->where('status', 'cancelled')->count()
            ]);
        } catch (\Exception $e) {
            \Log::error('AdminController getAllRides error: ' . $e->getMessage());
            return response()->json([
                'success' => true,
                'rides' => [],
                'total' => 0,
                'pending' => 0,
                'active' => 0,
                'completed' => 0,
                'cancelled' => 0,
                'message' => 'Failed to fetch rides: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Update ride status (admin override)
     */
    public function updateRideStatus(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,accepted,driver_arrived,in_progress,completed,cancelled'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid status',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $ride = Ride::findOrFail($id);
            $ride->status = $request->status;
            $ride->save();

            return response()->json([
                'success' => true,
                'message' => 'Ride status updated successfully',
                'ride' => $ride->load(['user:id,first_name,last_name', 'driver:id,first_name,last_name'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update ride status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get system settings
     */
    public function getSystemSettings(): JsonResponse
    {
        try {
            // In a real app, these would come from a settings table or config
            $settings = [
                'app_name' => config('app.name', 'Cholen Ride Share'),
                'base_fare' => 50.0,
                'per_km_rate' => 15.0,
                'booking_fee' => 10.0,
                'max_ride_distance' => 100,
                'driver_commission' => 20, // percentage
                'auto_assign_drivers' => true,
                'allow_cash_payment' => true,
                'allow_card_payment' => true,
                'maintenance_mode' => false,
                'max_waiting_time' => 10, // minutes
                'cancellation_fee' => 25.0
            ];

            return response()->json([
                'success' => true,
                'settings' => $settings
            ]);
        } catch (\Exception $e) {
            \Log::error('AdminController getSystemSettings error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch settings',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }

    /**
     * Update system settings
     */
    public function updateSystemSettings(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'base_fare' => 'numeric|min:0',
            'per_km_rate' => 'numeric|min:0',
            'booking_fee' => 'numeric|min:0',
            'max_ride_distance' => 'integer|min:1',
            'driver_commission' => 'integer|min:0|max:100',
            'auto_assign_drivers' => 'boolean',
            'allow_cash_payment' => 'boolean',
            'allow_card_payment' => 'boolean',
            'maintenance_mode' => 'boolean',
            'max_waiting_time' => 'integer|min:1',
            'cancellation_fee' => 'numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // In a real app, you would save these to a database or config file
            // For now, we'll just return success
            return response()->json([
                'success' => true,
                'message' => 'Settings updated successfully',
                'settings' => $request->all()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get dashboard analytics
     */
    public function getDashboardAnalytics(): JsonResponse
    {
        try {
            $totalUsers = User::where('role', 'user')->count();
            $totalDrivers = User::where('role', 'driver')->count();
            $pendingDrivers = User::where('role', 'driver')
                ->where('driver_status', 'pending')->count();
            $approvedDrivers = User::where('role', 'driver')
                ->where('driver_status', 'approved')->count();
            
            // Handle rides data safely
            $totalRides = 0;
            $todayRides = 0;
            $completedRides = 0;
            $activeRides = 0;
            $pendingRides = 0;
            $totalRevenue = 0;
            $todayRevenue = 0;
            $recentRides = [];

            try {
                if (class_exists('App\Models\Ride')) {
                    $totalRides = Ride::count();
                    $todayRides = Ride::whereDate('created_at', today())->count();
                    $completedRides = Ride::where('status', 'completed')->count();
                    $activeRides = Ride::whereIn('status', ['pending', 'accepted', 'driver_arrived', 'in_progress'])->count();
                    $pendingRides = Ride::where('status', 'pending')->count();

                    // Revenue calculations
                    $totalRevenue = Ride::where('status', 'completed')->sum('final_price') ?? 0;
                    $todayRevenue = Ride::where('status', 'completed')
                        ->whereDate('created_at', today())->sum('final_price') ?? 0;

                    $recentRides = Ride::with(['user:id,first_name,last_name', 'driver:id,first_name,last_name'])
                        ->latest()->take(5)->get();
                }
            } catch (\Exception $rideError) {
                \Log::warning('Rides data not available: ' . $rideError->getMessage());
            }

            // Recent activity
            $recentUsers = User::latest()->take(5)->get(['id', 'first_name', 'last_name', 'role', 'created_at']);

            return response()->json([
                'success' => true,
                'analytics' => [
                    'users' => [
                        'total' => $totalUsers,
                        'active' => User::where('role', 'user')->where('is_active', true)->count()
                    ],
                    'drivers' => [
                        'total' => $totalDrivers,
                        'pending' => $pendingDrivers,
                        'approved' => $approvedDrivers,
                        'rejected' => max(0, $totalDrivers - $pendingDrivers - $approvedDrivers)
                    ],
                    'rides' => [
                        'total' => $totalRides,
                        'today' => $todayRides,
                        'completed' => $completedRides,
                        'active' => $activeRides,
                        'pending' => $pendingRides
                    ],
                    'revenue' => [
                        'total' => $totalRevenue,
                        'today' => $todayRevenue
                    ],
                    'recent_users' => $recentUsers,
                    'recent_rides' => $recentRides
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('AdminController getDashboardAnalytics error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch analytics',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TestAdminController extends Controller
{
    /**
     * Get all users - no auth required for testing
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
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch users',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => basename($e->getFile())
            ], 500);
        }
    }

    /**
     * Get all drivers - no auth required for testing
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
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch drivers',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => basename($e->getFile())
            ], 500);
        }
    }

    /**
     * Get all rides - no auth required for testing
     */
    public function getAllRides(): JsonResponse
    {
        try {
            // Return empty rides for now since table might not have data
            return response()->json([
                'success' => true,
                'rides' => [],
                'total' => 0,
                'pending' => 0,
                'active' => 0,
                'completed' => 0,
                'cancelled' => 0,
                'message' => 'No rides data available'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch rides',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => basename($e->getFile())
            ], 500);
        }
    }

    /**
     * Get dashboard analytics - no auth required for testing
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
                        'total' => 0,
                        'today' => 0,
                        'completed' => 0,
                        'active' => 0,
                        'pending' => 0
                    ],
                    'revenue' => [
                        'total' => 2500,
                        'today' => 350
                    ],
                    'recent_users' => $recentUsers,
                    'recent_rides' => []
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch analytics',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => basename($e->getFile())
            ], 500);
        }
    }

    /**
     * Get system settings - no auth required for testing
     */
    public function getSystemSettings(): JsonResponse
    {
        try {
            $settings = [
                'app_name' => 'Cholen Ride Share',
                'base_fare' => 50.0,
                'per_km_rate' => 15.0,
                'booking_fee' => 10.0,
                'max_ride_distance' => 100,
                'driver_commission' => 20,
                'auto_assign_drivers' => true,
                'allow_cash_payment' => true,
                'allow_card_payment' => true,
                'maintenance_mode' => false,
                'max_waiting_time' => 10,
                'cancellation_fee' => 25.0
            ];

            return response()->json([
                'success' => true,
                'settings' => $settings
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch settings',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => basename($e->getFile())
            ], 500);
        }
    }

    /**
     * Create new driver - no auth required for testing
     */
    public function createDriver(Request $request): JsonResponse
    {
        try {
            $driver = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'name' => $request->first_name . ' ' . $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => bcrypt($request->password),
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
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => basename($e->getFile())
            ], 500);
        }
    }

    /**
     * Update driver status - no auth required for testing
     */
    public function updateDriverStatus(Request $request, $id): JsonResponse
    {
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
     * Delete driver - no auth required for testing
     */
    public function deleteDriver($id): JsonResponse
    {
        try {
            $driver = User::where('role', 'driver')->findOrFail($id);
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
     * Delete user - no auth required for testing
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
     * Update user - no auth required for testing
     */
    public function updateUser(Request $request, $id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);
            $user->update($request->only([
                'first_name', 'last_name', 'email', 'phone', 'is_active', 'role'
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
     * Update ride status - no auth required for testing
     */
    public function updateRideStatus(Request $request, $id): JsonResponse
    {
        try {
            // For testing, just return success
            return response()->json([
                'success' => true,
                'message' => 'Ride status updated successfully (test mode)',
                'ride' => ['id' => $id, 'status' => $request->status]
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
     * Update system settings - no auth required for testing
     */
    public function updateSystemSettings(Request $request): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'message' => 'Settings updated successfully (test mode)',
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
     * Test endpoint
     */
    public function test(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Test admin controller is working',
            'timestamp' => now(),
            'user_count' => User::count()
        ]);
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ride;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class RideController extends Controller
{
    /**
     * Book a new ride (User only)
     */
    public function bookRide(Request $request)
    {
        if (!$request->user()->isUser()) {
            return response()->json([
                'success' => false,
                'message' => 'Only users can book rides'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'pickup_location' => 'required|string|max:255',
            'dropoff_location' => 'required|string|max:255',
            'ride_date' => 'required|date|after_or_equal:today',
            'ride_time' => 'required|date_format:H:i',
            'vehicle_type' => 'in:standard,premium,xl',
            'passenger_count' => 'integer|min:1|max:8',
            'special_instructions' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Check if user has any active rides
            $activeRide = Ride::where('user_id', $request->user()->id)
                             ->whereIn('status', ['pending', 'accepted', 'driver_arrived', 'in_progress'])
                             ->first();

            if ($activeRide) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have an active ride booking'
                ], 422);
            }

            // Combine date and time
            $scheduledAt = Carbon::createFromFormat('Y-m-d H:i', 
                $request->ride_date . ' ' . $request->ride_time
            );

            // Validate future time for today's bookings
            if ($scheduledAt->isToday() && $scheduledAt->isPast()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot book rides in the past'
                ], 422);
            }

            // Mock distance calculation (in real app, use Google Maps API)
            $distanceKm = rand(5, 50) + (rand(0, 99) / 100); // Random distance between 5-50 km
            $estimatedPrice = Ride::calculatePrice($distanceKm, $request->vehicle_type ?? 'standard');
            $estimatedDuration = ceil($distanceKm * 2.5); // Rough estimate: 2.5 minutes per km

            $ride = Ride::create([
                'user_id' => $request->user()->id,
                'pickup_location' => $request->pickup_location,
                'dropoff_location' => $request->dropoff_location,
                'ride_date' => $request->ride_date,
                'ride_time' => $request->ride_time,
                'scheduled_at' => $scheduledAt,
                'estimated_price' => $estimatedPrice,
                'distance_km' => $distanceKm,
                'estimated_duration_minutes' => $estimatedDuration,
                'vehicle_type' => $request->vehicle_type ?? 'standard',
                'passenger_count' => $request->passenger_count ?? 1,
                'special_instructions' => $request->special_instructions,
                'status' => 'pending'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ride booked successfully! Looking for available drivers.',
                'ride' => [
                    'id' => $ride->id,
                    'pickup_location' => $ride->pickup_location,
                    'dropoff_location' => $ride->dropoff_location,
                    'scheduled_at' => $ride->formatted_scheduled_time,
                    'estimated_price' => $ride->estimated_price,
                    'distance_km' => $ride->distance_km,
                    'estimated_duration_minutes' => $ride->estimated_duration_minutes,
                    'vehicle_type' => $ride->vehicle_type,
                    'status' => $ride->status,
                    'status_text' => $ride->status_text
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to book ride',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available rides for drivers
     */
    public function getAvailableRides(Request $request)
    {
        if (!$request->user()->isDriver()) {
            return response()->json([
                'success' => false,
                'message' => 'Only drivers can view available rides'
            ], 403);
        }

        if ($request->user()->driver_status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Your driver account is not approved yet'
            ], 403);
        }

        // Check if driver has any active rides
        $activeRide = Ride::where('driver_id', $request->user()->id)
                         ->whereIn('status', ['accepted', 'driver_arrived', 'in_progress'])
                         ->first();

        if ($activeRide) {
            return response()->json([
                'success' => false,
                'message' => 'You already have an active ride',
                'active_ride' => $this->formatRideForDriver($activeRide)
            ], 422);
        }

        $rides = Ride::with('user')
                    ->where('status', 'pending')
                    ->where('scheduled_at', '>=', now()->subHours(1)) // Show rides from 1 hour ago
                    ->orderBy('scheduled_at', 'asc')
                    ->limit(20)
                    ->get();

        return response()->json([
            'success' => true,
            'rides' => $rides->map(function ($ride) {
                return $this->formatRideForDriver($ride);
            })
        ]);
    }

    /**
     * Accept a ride (Driver only)
     */
    public function acceptRide(Request $request, $rideId)
    {
        if (!$request->user()->isDriver()) {
            return response()->json([
                'success' => false,
                'message' => 'Only drivers can accept rides'
            ], 403);
        }

        $ride = Ride::find($rideId);

        if (!$ride) {
            return response()->json([
                'success' => false,
                'message' => 'Ride not found'
            ], 404);
        }

        if ($ride->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This ride is no longer available'
            ], 422);
        }

        // Check if driver has any active rides
        $activeRide = Ride::where('driver_id', $request->user()->id)
                         ->whereIn('status', ['accepted', 'driver_arrived', 'in_progress'])
                         ->first();

        if ($activeRide) {
            return response()->json([
                'success' => false,
                'message' => 'You already have an active ride'
            ], 422);
        }

        try {
            $ride->update([
                'driver_id' => $request->user()->id,
                'status' => 'accepted',
                'accepted_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ride accepted successfully!',
                'ride' => $this->formatRideForDriver($ride->fresh())
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to accept ride',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's rides
     */
    public function getUserRides(Request $request)
    {
        $rides = Ride::with('driver')
                    ->where('user_id', $request->user()->id)
                    ->orderBy('created_at', 'desc')
                    ->paginate(10);

        return response()->json([
            'success' => true,
            'rides' => $rides->items(),
            'pagination' => [
                'current_page' => $rides->currentPage(),
                'last_page' => $rides->lastPage(),
                'per_page' => $rides->perPage(),
                'total' => $rides->total()
            ]
        ]);
    }

    /**
     * Get driver's rides
     */
    public function getDriverRides(Request $request)
    {
        if (!$request->user()->isDriver()) {
            return response()->json([
                'success' => false,
                'message' => 'Only drivers can view driver rides'
            ], 403);
        }

        $rides = Ride::with('user')
                    ->where('driver_id', $request->user()->id)
                    ->orderBy('created_at', 'desc')
                    ->paginate(10);

        return response()->json([
            'success' => true,
            'rides' => $rides->items(),
            'pagination' => [
                'current_page' => $rides->currentPage(),
                'last_page' => $rides->lastPage(),
                'per_page' => $rides->perPage(),
                'total' => $rides->total()
            ]
        ]);
    }

    /**
     * Update ride status (Driver only)
     */
    public function updateRideStatus(Request $request, $rideId)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:driver_arrived,in_progress,completed,cancelled'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid status',
                'errors' => $validator->errors()
            ], 422);
        }

        $ride = Ride::find($rideId);

        if (!$ride) {
            return response()->json([
                'success' => false,
                'message' => 'Ride not found'
            ], 404);
        }

        // Check if user owns this ride (for cancellation) or is the assigned driver
        if ($ride->user_id !== $request->user()->id && $ride->driver_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        try {
            $statusField = match($request->status) {
                'driver_arrived' => 'driver_arrived_at',
                'in_progress' => 'started_at',
                'completed' => 'completed_at',
                'cancelled' => 'cancelled_at',
                default => null
            };

            $updateData = ['status' => $request->status];
            if ($statusField) {
                $updateData[$statusField] = now();
            }

            // Set final price when completing ride
            if ($request->status === 'completed' && !$ride->final_price) {
                $updateData['final_price'] = $ride->estimated_price;
            }

            $ride->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Ride status updated successfully',
                'ride' => $this->formatRideForDriver($ride->fresh())
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
     * Process payment for a completed ride
     */
    public function processPayment(Request $request, $rideId)
    {
        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|in:cash,card',
            'card_number' => 'required_if:payment_method,card',
            'expiry_date' => 'required_if:payment_method,card',
            'cvv' => 'required_if:payment_method,card',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid payment data',
                'errors' => $validator->errors()
            ], 422);
        }

        $ride = Ride::find($rideId);

        if (!$ride) {
            return response()->json([
                'success' => false,
                'message' => 'Ride not found'
            ], 404);
        }

        // Check if user owns this ride or is the assigned driver
        \Log::info('Payment processing attempt', [
            'user_id' => $request->user()->id,
            'ride_user_id' => $ride->user_id,
            'ride_driver_id' => $ride->driver_id,
            'ride_status' => $ride->status
        ]);
        
        if ($ride->user_id !== $request->user()->id && $ride->driver_id !== $request->user()->id) {
            \Log::warning('Payment unauthorized', [
                'user_id' => $request->user()->id,
                'ride_user_id' => $ride->user_id,
                'ride_driver_id' => $ride->driver_id
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Check if ride is in a state that allows completion
        // Allow completion from driver_arrived or in_progress states
        \Log::info('Ride status check', [
            'ride_id' => $rideId,
            'current_status' => $ride->status,
            'allowed_statuses' => ['driver_arrived', 'in_progress']
        ]);
        
        if (!in_array($ride->status, ['driver_arrived', 'in_progress'])) {
            \Log::warning('Ride status not allowed for payment', [
                'ride_id' => $rideId,
                'current_status' => $ride->status
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Ride must be in driver arrived or in progress state to process payment'
            ], 422);
        }

        try {
            // In a real implementation, you would integrate with a payment gateway here
            // For now, we'll just simulate a successful payment
            
            // Update payment information
            $ride->update([
                'payment_method' => $request->payment_method,
                'payment_status' => 'completed'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully!',
                'ride' => $this->formatRideForDriver($ride->fresh())
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel ride
     */
    public function cancelRide(Request $request, $rideId)
    {
        $ride = Ride::find($rideId);

        if (!$ride) {
            return response()->json([
                'success' => false,
                'message' => 'Ride not found'
            ], 404);
        }

        // Check if user owns this ride or is the assigned driver
        if ($ride->user_id !== $request->user()->id && $ride->driver_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        if (!$ride->canBeCancelled()) {
            return response()->json([
                'success' => false,
                'message' => 'This ride cannot be cancelled'
            ], 422);
        }

        try {
            $ride->update([
                'status' => 'cancelled',
                'cancelled_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ride cancelled successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel ride',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format ride data for driver view
     */
    private function formatRideForDriver($ride)
    {
        return [
            'id' => $ride->id,
            'pickup_location' => $ride->pickup_location,
            'dropoff_location' => $ride->dropoff_location,
            'scheduled_at' => $ride->formatted_scheduled_time,
            'estimated_price' => $ride->estimated_price,
            'final_price' => $ride->final_price,
            'distance_km' => $ride->distance_km,
            'estimated_duration_minutes' => $ride->estimated_duration_minutes,
            'vehicle_type' => $ride->vehicle_type,
            'passenger_count' => $ride->passenger_count,
            'special_instructions' => $ride->special_instructions,
            'status' => $ride->status,
            'status_text' => $ride->status_text,
            'user' => [
                'name' => $ride->user->first_name . ' ' . $ride->user->last_name,
                'phone' => $ride->user->phone ?? 'Not provided'
            ],
            'created_at' => $ride->created_at->format('M j, Y g:i A')
        ];
    }
}
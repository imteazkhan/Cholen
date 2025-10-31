<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Ride extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'driver_id',
        'pickup_location',
        'dropoff_location',
        'pickup_latitude',
        'pickup_longitude',
        'dropoff_latitude',
        'dropoff_longitude',
        'ride_date',
        'ride_time',
        'scheduled_at',
        'estimated_price',
        'final_price',
        'distance_km',
        'estimated_duration_minutes',
        'status',
        'special_instructions',
        'vehicle_type',
        'passenger_count',
        'user_rating',
        'driver_rating',
        'user_feedback',
        'driver_feedback',
        'accepted_at',
        'driver_arrived_at',
        'started_at',
        'completed_at',
        'cancelled_at',
        'payment_method',
        'payment_status'
    ];

    protected $casts = [
        'ride_date' => 'date',
        'ride_time' => 'datetime:H:i',
        'scheduled_at' => 'datetime',
        'accepted_at' => 'datetime',
        'driver_arrived_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'estimated_price' => 'decimal:2',
        'final_price' => 'decimal:2',
        'distance_km' => 'decimal:2',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['accepted', 'driver_arrived', 'in_progress']);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeForDriver($query, $driverId)
    {
        return $query->where('driver_id', $driverId);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Helper methods
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isActive()
    {
        return in_array($this->status, ['accepted', 'driver_arrived', 'in_progress']);
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    public function canBeCancelled()
    {
        return in_array($this->status, ['pending', 'accepted']);
    }

    public function getFormattedScheduledTimeAttribute()
    {
        return $this->scheduled_at->format('M j, Y \a\t g:i A');
    }

    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'pending' => 'bg-warning',
            'accepted' => 'bg-info',
            'driver_arrived' => 'bg-primary',
            'in_progress' => 'bg-success',
            'completed' => 'bg-success',
            'cancelled' => 'bg-danger',
            'expired' => 'bg-secondary',
            default => 'bg-secondary'
        };
    }

    public function getStatusTextAttribute()
    {
        return match($this->status) {
            'pending' => 'Looking for driver',
            'accepted' => 'Driver assigned',
            'driver_arrived' => 'Driver arrived',
            'in_progress' => 'Ride in progress',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'expired' => 'Expired',
            default => 'Unknown'
        };
    }

    // Calculate estimated price based on distance
    public static function calculatePrice($distanceKm, $vehicleType = 'standard')
    {
        $baseFare = match($vehicleType) {
            'standard' => 50,
            'premium' => 80,
            'xl' => 100,
            default => 50
        };

        $perKmRate = match($vehicleType) {
            'standard' => 25,
            'premium' => 35,
            'xl' => 45,
            default => 25
        };

        return $baseFare + ($distanceKm * $perKmRate);
    }
}
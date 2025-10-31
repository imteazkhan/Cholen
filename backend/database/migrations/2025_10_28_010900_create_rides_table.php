<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // User who booked the ride
            $table->foreignId('driver_id')->nullable()->constrained('users')->onDelete('set null'); // Driver assigned
            
            // Ride details
            $table->string('pickup_location');
            $table->string('dropoff_location');
            $table->decimal('pickup_latitude', 10, 8)->nullable();
            $table->decimal('pickup_longitude', 11, 8)->nullable();
            $table->decimal('dropoff_latitude', 10, 8)->nullable();
            $table->decimal('dropoff_longitude', 11, 8)->nullable();
            
            // Scheduling
            $table->date('ride_date');
            $table->time('ride_time');
            $table->datetime('scheduled_at'); // Combined date and time
            
            // Pricing and distance
            $table->decimal('estimated_price', 8, 2)->nullable();
            $table->decimal('final_price', 8, 2)->nullable();
            $table->decimal('distance_km', 8, 2)->nullable();
            $table->integer('estimated_duration_minutes')->nullable();
            
            // Status tracking
            $table->enum('status', [
                'pending',      // Waiting for driver
                'accepted',     // Driver accepted
                'driver_arrived', // Driver at pickup
                'in_progress',  // Ride started
                'completed',    // Ride finished
                'cancelled',    // Cancelled by user/driver
                'expired'       // No driver found
            ])->default('pending');
            
            // Ride tracking
            $table->datetime('accepted_at')->nullable();
            $table->datetime('driver_arrived_at')->nullable();
            $table->datetime('started_at')->nullable();
            $table->datetime('completed_at')->nullable();
            $table->datetime('cancelled_at')->nullable();
            
            // Additional info
            $table->text('special_instructions')->nullable();
            $table->string('vehicle_type')->default('standard'); // standard, premium, xl
            $table->integer('passenger_count')->default(1);
            
            // Ratings and feedback
            $table->integer('user_rating')->nullable(); // 1-5 stars
            $table->integer('driver_rating')->nullable(); // 1-5 stars
            $table->text('user_feedback')->nullable();
            $table->text('driver_feedback')->nullable();
            
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['user_id', 'status']);
            $table->index(['driver_id', 'status']);
            $table->index(['status', 'scheduled_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rides');
    }
};
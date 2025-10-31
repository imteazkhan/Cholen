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
        Schema::table('users', function (Blueprint $table) {
            // Add email field for Vue frontend authentication
            $table->string('email')->unique()->nullable()->after('name');
            $table->timestamp('email_verified_at')->nullable()->after('email');
            
            // Add password field for traditional authentication
            $table->string('password')->nullable()->after('email_verified_at');
            
            // Add role-based fields
            $table->enum('role', ['user', 'driver', 'admin'])->default('user')->after('password');
            $table->string('first_name')->nullable()->after('role');
            $table->string('last_name')->nullable()->after('first_name');
            
            // Driver-specific fields
            $table->string('driver_license')->nullable()->after('last_name');
            $table->enum('driver_status', ['pending', 'approved', 'suspended'])->nullable()->after('driver_license');
            
            // Admin fields
            $table->json('permissions')->nullable()->after('driver_status');
            
            // Profile fields
            $table->text('bio')->nullable()->after('permissions');
            $table->string('profile_image')->nullable()->after('bio');
            
            // Status fields
            $table->boolean('is_active')->default(true)->after('profile_image');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'email',
                'email_verified_at',
                'password',
                'role',
                'first_name',
                'last_name',
                'driver_license',
                'driver_status',
                'permissions',
                'bio',
                'profile_image',
                'is_active',
                'last_login_at'
            ]);
        });
    }
};

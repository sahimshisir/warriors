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
        Schema::create('user_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Foreign key referencing users table
            
            // Usage Data
            $table->timestamp('last_login_at')->nullable(); // Login times and frequency
            $table->json('devices')->nullable(); // Devices used
            $table->json('browsing_activity')->nullable(); // Browsing activity

            $table->json('blocked_users')->nullable(); // Blocked users or accounts
            $table->boolean('two_factor_enabled')->default(false); // Two-factor authentication settings

          

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_details');
    }
};

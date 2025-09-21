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
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->enum('theme', ['light', 'dark', 'system'])->default('system');
            $table->string('primary_color', 7)->default('#3B82F6'); // hex color
            $table->string('timezone', 50)->default('UTC');
            $table->enum('date_format', ['Y-m-d', 'm/d/Y', 'd/m/Y', 'd-m-Y'])->default('Y-m-d');
            $table->enum('time_format', ['H:i', 'g:i A', 'h:i A'])->default('H:i');
            $table->string('language', 5)->default('en');
            $table->boolean('email_notifications')->default(true);
            $table->boolean('push_notifications')->default(false);
            $table->json('dashboard_layout')->nullable(); // customizable dashboard widgets
            $table->json('notification_settings')->nullable(); // detailed notification preferences
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
    }
};

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
        Schema::create('habits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('frequency', ['daily', 'weekly', 'monthly'])->default('daily');
            $table->json('frequency_details')->nullable(); // specific days for weekly, dates for monthly
            $table->integer('target_count')->default(1); // how many times per frequency period
            $table->string('unit')->nullable(); // times, minutes, pages, etc
            $table->boolean('is_active')->default(true);
            $table->date('start_date');
            $table->integer('current_streak')->default(0);
            $table->integer('longest_streak')->default(0);
            $table->date('last_completed_date')->nullable();
            $table->integer('total_completions')->default(0);
            $table->string('reminder_time', 8)->nullable(); // HH:MM:SS format
            $table->boolean('reminder_enabled')->default(false);
            $table->timestamps();
            
            $table->index(['user_id', 'is_active']);
            $table->index(['user_id', 'frequency']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('habits');
    }
};

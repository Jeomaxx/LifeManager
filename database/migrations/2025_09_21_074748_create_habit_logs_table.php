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
        Schema::create('habit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('habit_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('completion_date');
            $table->integer('value')->default(1); // how many times completed that day
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable(); // extra data like mood, difficulty, etc
            $table->timestamps();
            
            $table->unique(['habit_id', 'completion_date']); // one log per habit per day
            $table->index(['user_id', 'completion_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('habit_logs');
    }
};

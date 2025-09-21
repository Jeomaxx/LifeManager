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
        Schema::create('goal_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goal_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('progress_value', 10, 2); // current progress value
            $table->text('notes')->nullable();
            $table->date('progress_date');
            $table->enum('update_type', ['manual', 'automatic', 'milestone'])->default('manual');
            $table->json('metadata')->nullable(); // additional context data
            $table->timestamps();
            
            $table->index(['goal_id', 'progress_date']);
            $table->index(['user_id', 'progress_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goal_progress');
    }
};

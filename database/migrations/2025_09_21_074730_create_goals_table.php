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
        Schema::create('goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['personal', 'professional', 'health', 'learning', 'other'])->default('other');
            $table->enum('status', ['not_started', 'in_progress', 'completed', 'on_hold', 'cancelled'])->default('not_started');
            $table->date('start_date')->nullable();
            $table->date('target_date')->nullable();
            $table->date('completed_date')->nullable();
            $table->decimal('target_value', 10, 2)->nullable(); // for measurable goals
            $table->decimal('current_value', 10, 2)->default(0); // progress tracking
            $table->string('unit')->nullable(); // kg, hours, books, etc
            $table->integer('progress_percentage')->default(0);
            $table->boolean('is_archived')->default(false);
            $table->json('milestones')->nullable(); // store milestone data
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'type']);
            $table->index(['user_id', 'target_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goals');
    }
};

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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->timestamp('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->json('recurrence_pattern')->nullable(); // stores recurring settings
            $table->foreignId('parent_task_id')->nullable()->constrained('tasks')->onDelete('cascade'); // for recurring tasks
            $table->integer('estimated_minutes')->nullable();
            $table->integer('actual_minutes')->nullable();
            $table->json('metadata')->nullable(); // for future extensibility
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'due_date']);
            $table->index(['user_id', 'priority']);
            $table->index(['category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};

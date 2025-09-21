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
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('task_id')->nullable()->constrained()->onDelete('cascade'); // optional link to task
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamp('start_datetime');
            $table->timestamp('end_datetime');
            $table->boolean('is_all_day')->default(false);
            $table->string('location')->nullable();
            $table->enum('type', ['event', 'reminder', 'meeting', 'deadline', 'other'])->default('event');
            $table->string('color', 7)->default('#3B82F6'); // hex color
            $table->boolean('is_recurring')->default(false);
            $table->json('recurrence_pattern')->nullable();
            $table->foreignId('parent_event_id')->nullable()->constrained('calendar_events')->onDelete('cascade');
            $table->json('reminder_settings')->nullable(); // notification preferences
            $table->timestamps();
            
            $table->index(['user_id', 'start_datetime']);
            $table->index(['user_id', 'type']);
            $table->index(['task_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendar_events');
    }
};

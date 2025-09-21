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
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->longText('content'); // rich text content
            $table->enum('type', ['note', 'journal', 'idea', 'meeting', 'other'])->default('note');
            $table->boolean('is_favorite')->default(false);
            $table->boolean('is_archived')->default(false);
            $table->timestamp('archived_at')->nullable();
            $table->json('formatting_data')->nullable(); // for rich text formatting
            $table->timestamps();
            
            $table->index(['user_id', 'type']);
            $table->index(['user_id', 'is_favorite']);
            $table->index(['user_id', 'is_archived']);
            // PostgreSQL full-text search will be handled through GIN indexes if needed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};

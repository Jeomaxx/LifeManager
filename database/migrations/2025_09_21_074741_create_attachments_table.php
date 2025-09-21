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
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->morphs('attachable'); // polymorphic relation (tasks, notes, goals, etc)
            $table->string('original_name');
            $table->string('filename'); // stored filename
            $table->string('file_path');
            $table->string('mime_type');
            $table->bigInteger('file_size'); // in bytes
            $table->string('file_hash', 64)->nullable(); // for duplicate detection
            $table->timestamps();
            
            // morphs() already creates the attachable_type, attachable_id index
            $table->index(['user_id', 'mime_type']);
            $table->index(['file_hash']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};

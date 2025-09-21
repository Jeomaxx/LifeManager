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
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('color', 7)->default('#6B7280'); // hex color for tag styling
            $table->text('description')->nullable();
            $table->integer('usage_count')->default(0); // track how often used
            $table->timestamps();
            
            $table->unique(['user_id', 'name']);
            $table->index(['user_id', 'usage_count']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tags');
    }
};

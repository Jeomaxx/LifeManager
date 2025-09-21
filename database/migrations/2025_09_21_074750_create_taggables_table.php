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
        Schema::create('taggables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tag_id')->constrained()->onDelete('cascade');
            $table->morphs('taggable'); // polymorphic relation (tasks, notes, goals, etc)
            $table->timestamps();
            
            $table->unique(['tag_id', 'taggable_id', 'taggable_type'], 'tag_taggable_unique');
            // morphs() already creates the taggable_type, taggable_id index
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taggables');
    }
};

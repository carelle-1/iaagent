<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('user_agent_publication_visuals');

        Schema::create('user_agent_publication_visuals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calendar_entry_id')->constrained('user_agent_calendar_entries')->cascadeOnDelete();
            $table->text('prompt');
            $table->text('negative_prompt')->nullable();
            $table->string('image_path');
            $table->string('image_name')->nullable();
            $table->string('model')->nullable();
            $table->string('theme')->nullable();
            $table->string('status')->default('generated');
            $table->text('error')->nullable();
            $table->timestamps();

            $table->index(['calendar_entry_id', 'created_at'], 'pub_visuals_entry_created_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_agent_publication_visuals');
    }
};
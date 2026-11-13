<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_agent_calendar_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_agent_id')->constrained()->cascadeOnDelete();
            $table->dateTime('published_at');
            $table->string('platform')->default('facebook');
            $table->string('pillar')->nullable();
            $table->string('title')->nullable();
            $table->text('caption')->nullable();
            $table->text('visual_prompt')->nullable();
            $table->string('hashtags')->nullable();
            $table->string('status')->default('pending');
            $table->string('model')->nullable();
            $table->timestamps();

            $table->index(['user_agent_id', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_agent_calendar_entries');
    }
};
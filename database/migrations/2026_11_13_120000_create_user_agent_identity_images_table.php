<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_agent_identity_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_agent_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('caption')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['user_agent_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_agent_identity_images');
    }
};
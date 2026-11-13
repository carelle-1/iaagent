<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_agent_inspiring_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_agent_id')->constrained()->cascadeOnDelete();
            $table->string('platform')->default('instagram');
            $table->string('handle');
            $table->string('url')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('user_agent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_agent_inspiring_accounts');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_agent_identities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_agent_id')->constrained()->cascadeOnDelete();
            $table->text('objective')->nullable();
            $table->json('palette')->nullable();
            $table->text('charter')->nullable();
            $table->string('logo_path')->nullable();
            $table->timestamps();

            $table->unique('user_agent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_agent_identities');
    }
};
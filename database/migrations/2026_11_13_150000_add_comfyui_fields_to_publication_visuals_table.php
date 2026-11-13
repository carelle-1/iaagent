<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_agent_publication_visuals', function (Blueprint $table) {
            $table->string('prompt_id')->nullable()->after('model');
            $table->string('client_id')->nullable()->after('prompt_id');
            $table->string('image_path')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('user_agent_publication_visuals', function (Blueprint $table) {
            $table->dropColumn(['prompt_id', 'client_id']);
        });
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_agent_publication_visuals', function (Blueprint $table) {
            $table->json('visual_direction')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('user_agent_publication_visuals', function (Blueprint $table) {
            $table->dropColumn('visual_direction');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_agent_publication_visuals', function (Blueprint $table) {
            $table->string('visual_subject')->nullable();
            $table->string('visual_style')->nullable();
            $table->string('aspect_ratio', 16)->nullable();
            $table->string('comfy_filename')->nullable();
            $table->string('comfy_subfolder')->nullable();
            $table->string('comfy_type', 32)->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('user_agent_publication_visuals', function (Blueprint $table) {
            $table->dropColumn([
                'visual_subject',
                'visual_style',
                'aspect_ratio',
                'comfy_filename',
                'comfy_subfolder',
                'comfy_type',
                'started_at',
                'completed_at',
            ]);
        });
    }
};

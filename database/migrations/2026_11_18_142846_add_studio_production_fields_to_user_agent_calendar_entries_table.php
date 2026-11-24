<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_agent_calendar_entries', function (Blueprint $table) {
            // Studio production fields (separate from calendar planning fields)
            $table->text('studio_caption')->nullable()->after('generated_content');
            $table->string('studio_hashtags')->nullable()->after('studio_caption');
            $table->text('studio_visual_prompt')->nullable()->after('studio_hashtags');
            $table->string('studio_cta')->nullable()->after('studio_visual_prompt');
            $table->timestamp('studio_generated_at')->nullable()->after('studio_cta');
            $table->boolean('studio_generated')->default(false)->after('studio_generated_at');
        });
    }

    public function down(): void
    {
        Schema::table('user_agent_calendar_entries', function (Blueprint $table) {
            $table->dropColumn([
                'studio_caption',
                'studio_hashtags',
                'studio_visual_prompt',
                'studio_cta',
                'studio_generated_at',
                'studio_generated',
            ]);
        });
    }
};
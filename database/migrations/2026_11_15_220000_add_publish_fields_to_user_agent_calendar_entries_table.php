<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_agent_calendar_entries', function (Blueprint $table) {
            $table->foreignId('meta_account_id')->nullable()->after('model')->constrained('meta_accounts')->nullOnDelete();
            $table->string('platform_post_id')->nullable()->after('meta_account_id');
            $table->string('published_permalink', 1000)->nullable()->after('platform_post_id');
        });
    }

    public function down(): void
    {
        Schema::table('user_agent_calendar_entries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('meta_account_id');
            $table->dropColumn(['platform_post_id', 'published_permalink']);
        });
    }
};
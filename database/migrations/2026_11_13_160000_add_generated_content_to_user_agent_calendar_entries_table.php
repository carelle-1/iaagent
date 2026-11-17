<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_agent_calendar_entries', function (Blueprint $table) {
            $table->text('generated_content')->nullable()->after('caption');
        });
    }

    public function down(): void
    {
        Schema::table('user_agent_calendar_entries', function (Blueprint $table) {
            $table->dropColumn('generated_content');
        });
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_agent_identities', function (Blueprint $table) {
            $table->string('secteur_activite')->nullable()->after('charter');
            $table->string('localisation')->nullable()->after('secteur_activite');
            $table->text('cible')->nullable()->after('localisation');
        });
    }

    public function down(): void
    {
        Schema::table('user_agent_identities', function (Blueprint $table) {
            $table->dropColumn(['secteur_activite', 'localisation', 'cible']);
        });
    }
};
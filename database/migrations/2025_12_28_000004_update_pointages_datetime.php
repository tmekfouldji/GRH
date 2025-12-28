<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Vider la table (les données seront reseedées)
        DB::table('pointages')->truncate();
        
        // Modifier les colonnes de time vers datetime
        Schema::table('pointages', function (Blueprint $table) {
            $table->dropColumn(['heure_entree', 'heure_sortie']);
        });
        
        Schema::table('pointages', function (Blueprint $table) {
            $table->datetime('heure_entree')->nullable()->after('date_pointage');
            $table->datetime('heure_sortie')->nullable()->after('heure_entree');
        });
    }

    public function down(): void
    {
        DB::table('pointages')->truncate();
        
        Schema::table('pointages', function (Blueprint $table) {
            $table->dropColumn(['heure_entree', 'heure_sortie']);
        });
        
        Schema::table('pointages', function (Blueprint $table) {
            $table->time('heure_entree')->nullable()->after('date_pointage');
            $table->time('heure_sortie')->nullable()->after('heure_entree');
        });
    }
};

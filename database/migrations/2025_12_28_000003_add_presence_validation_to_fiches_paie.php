<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fiches_paie', function (Blueprint $table) {
            // Validation des présences
            $table->enum('statut_validation', ['en_attente', 'valide', 'ajuste'])->default('en_attente')->after('statut');
            $table->datetime('date_validation')->nullable()->after('statut_validation');
            $table->string('valide_par')->nullable()->after('date_validation');
            
            // Résumé des pointages calculés
            $table->integer('jours_travailles')->default(0)->after('heures_supplementaires');
            $table->integer('jours_absence')->default(0)->after('jours_travailles');
            $table->integer('jours_justifies')->default(0)->after('jours_absence');
            
            // Notes de validation
            $table->text('notes_validation')->nullable()->after('valide_par');
            
            // Ajustements manuels
            $table->decimal('ajustement_heures', 8, 2)->default(0)->after('notes_validation');
            $table->text('motif_ajustement')->nullable()->after('ajustement_heures');
        });
    }

    public function down(): void
    {
        Schema::table('fiches_paie', function (Blueprint $table) {
            $table->dropColumn([
                'statut_validation',
                'date_validation',
                'valide_par',
                'jours_travailles',
                'jours_absence',
                'jours_justifies',
                'notes_validation',
                'ajustement_heures',
                'motif_ajustement',
            ]);
        });
    }
};

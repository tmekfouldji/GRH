<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employes', function (Blueprint $table) {
            // Ajouter les colonnes manquantes si elles n'existent pas
            if (!Schema::hasColumn('employes', 'date_naissance')) {
                $table->date('date_naissance')->nullable()->after('date_embauche');
            }
            if (!Schema::hasColumn('employes', 'type_contrat')) {
                $table->string('type_contrat', 50)->nullable()->after('salaire_base');
            }
        });

        // Rendre certaines colonnes nullable si elles ne le sont pas
        // Note: MySQL permet de modifier les colonnes
        Schema::table('employes', function (Blueprint $table) {
            $table->string('prenom', 100)->nullable()->change();
            $table->date('date_embauche')->nullable()->change();
            $table->decimal('salaire_base', 10, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('employes', function (Blueprint $table) {
            if (Schema::hasColumn('employes', 'date_naissance')) {
                $table->dropColumn('date_naissance');
            }
            if (Schema::hasColumn('employes', 'type_contrat')) {
                $table->dropColumn('type_contrat');
            }
        });
    }
};

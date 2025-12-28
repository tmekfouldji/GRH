<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employes', function (Blueprint $table) {
            // Prime par défaut de l'employé (appliquée automatiquement)
            $table->decimal('prime_transport_defaut', 10, 2)->default(0)->after('salaire_base');
            $table->decimal('prime_panier_defaut', 10, 2)->default(0)->after('prime_transport_defaut');
            
            // Numéro de sécurité sociale algérien (CNAS)
            $table->string('numero_cnas', 30)->nullable()->after('cnss');
            
            // Mode de paiement
            $table->enum('mode_paiement', ['virement', 'especes', 'cheque'])->default('virement')->after('numero_cnas');
            
            // RIB bancaire
            $table->string('rib', 30)->nullable()->after('mode_paiement');
            
            // Catégorie employé (pour grille salariale)
            $table->string('categorie', 50)->nullable()->after('poste');
            
            // Échelon/Grade
            $table->string('echelon', 20)->nullable()->after('categorie');
        });
    }

    public function down(): void
    {
        Schema::table('employes', function (Blueprint $table) {
            $table->dropColumn([
                'prime_transport_defaut',
                'prime_panier_defaut', 
                'numero_cnas',
                'mode_paiement',
                'rib',
                'categorie',
                'echelon',
            ]);
        });
    }
};

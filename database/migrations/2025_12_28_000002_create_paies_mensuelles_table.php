<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Table principale pour les paies mensuelles (batch de paie)
        Schema::create('paies_mensuelles', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 50)->unique(); // Ex: PAIE-2024-12
            $table->integer('mois');
            $table->integer('annee');
            $table->integer('nombre_employes')->default(0);
            
            // Totaux
            $table->decimal('total_salaires_base', 15, 2)->default(0);
            $table->decimal('total_primes', 15, 2)->default(0);
            $table->decimal('total_brut', 15, 2)->default(0);
            $table->decimal('total_cotisations_cnas', 15, 2)->default(0);
            $table->decimal('total_irg', 15, 2)->default(0);
            $table->decimal('total_deductions', 15, 2)->default(0);
            $table->decimal('total_net', 15, 2)->default(0);
            
            // Charges patronales (25%)
            $table->decimal('total_charges_patronales', 15, 2)->default(0);
            $table->decimal('cout_total_employeur', 15, 2)->default(0);
            
            // Statuts
            $table->enum('statut', ['brouillon', 'valide', 'en_paiement', 'cloture'])->default('brouillon');
            $table->enum('statut_cnas', ['non_declare', 'declare', 'paye'])->default('non_declare');
            $table->enum('statut_irg', ['non_declare', 'declare', 'paye'])->default('non_declare');
            
            // Dates
            $table->date('date_creation');
            $table->date('date_validation')->nullable();
            $table->date('date_cloture')->nullable();
            
            // Notes
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            $table->unique(['mois', 'annee']);
        });
        
        // Ajouter le lien vers la paie mensuelle dans fiches_paie
        Schema::table('fiches_paie', function (Blueprint $table) {
            $table->foreignId('paie_mensuelle_id')->nullable()->after('id')->constrained('paies_mensuelles')->nullOnDelete();
            $table->enum('statut_reception', ['en_attente', 'remis', 'confirme'])->default('en_attente')->after('statut');
            $table->datetime('date_remise')->nullable()->after('statut_reception');
            $table->string('remis_par')->nullable()->after('date_remise');
        });
    }

    public function down(): void
    {
        Schema::table('fiches_paie', function (Blueprint $table) {
            $table->dropForeign(['paie_mensuelle_id']);
            $table->dropColumn(['paie_mensuelle_id', 'statut_reception', 'date_remise', 'remis_par']);
        });
        
        Schema::dropIfExists('paies_mensuelles');
    }
};

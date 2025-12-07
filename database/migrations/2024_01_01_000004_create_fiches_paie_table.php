<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fiches_paie', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employe_id')->constrained('employes')->onDelete('cascade');
            $table->integer('mois');
            $table->integer('annee');
            $table->decimal('salaire_base', 10, 2);
            $table->decimal('heures_normales', 5, 2)->default(0);
            $table->decimal('heures_supplementaires', 5, 2)->default(0);
            $table->decimal('prime_anciennete', 10, 2)->default(0);
            $table->decimal('prime_rendement', 10, 2)->default(0);
            $table->decimal('prime_transport', 10, 2)->default(0);
            $table->decimal('autres_primes', 10, 2)->default(0);
            $table->decimal('salaire_brut', 10, 2);
            $table->decimal('cotisation_cnss', 10, 2)->default(0);
            $table->decimal('cotisation_amo', 10, 2)->default(0);
            $table->decimal('ir', 10, 2)->default(0);
            $table->decimal('autres_deductions', 10, 2)->default(0);
            $table->decimal('total_deductions', 10, 2);
            $table->decimal('salaire_net', 10, 2);
            $table->enum('statut', ['brouillon', 'valide', 'paye'])->default('brouillon');
            $table->date('date_paiement')->nullable();
            $table->timestamps();

            $table->unique(['employe_id', 'mois', 'annee']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fiches_paie');
    }
};

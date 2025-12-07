<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employes', function (Blueprint $table) {
            $table->id();
            $table->string('matricule', 20)->unique(); // AC-No. - Code employé (obligatoire)
            $table->string('nom', 100);                 // Nom (obligatoire)
            $table->string('prenom', 100)->nullable();  // Prénom (optionnel)
            $table->string('email', 150)->nullable();
            $table->string('telephone', 20)->nullable();
            $table->string('poste', 100)->nullable();
            $table->string('departement', 100)->nullable();
            $table->date('date_embauche')->nullable();   // Nullable pour import
            $table->date('date_naissance')->nullable();
            $table->decimal('salaire_base', 10, 2)->nullable(); // Nullable pour import
            $table->string('type_contrat', 50)->nullable(); // CDI, CDD, etc.
            $table->enum('statut', ['actif', 'inactif', 'conge'])->default('actif');
            $table->string('adresse')->nullable();
            $table->string('cin', 20)->nullable();
            $table->string('cnss', 20)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employes');
    }
};

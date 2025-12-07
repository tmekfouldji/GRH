<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pointages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employe_id')->constrained('employes')->onDelete('cascade');
            $table->date('date_pointage');
            $table->time('heure_entree')->nullable();
            $table->time('heure_sortie')->nullable();
            $table->decimal('heures_travaillees', 5, 2)->default(0);
            $table->decimal('heures_supplementaires', 5, 2)->default(0);
            $table->enum('statut', ['present', 'absent', 'retard', 'conge', 'maladie', 'mission'])->default('present');
            $table->text('commentaire')->nullable();
            $table->timestamps();

            $table->unique(['employe_id', 'date_pointage']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pointages');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pointages', function (Blueprint $table) {
            // Changer time -> datetime pour inclure la date
            $table->dateTime('heure_entree')->nullable()->change();
            $table->dateTime('heure_sortie')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('pointages', function (Blueprint $table) {
            $table->time('heure_entree')->nullable()->change();
            $table->time('heure_sortie')->nullable()->change();
        });
    }
};

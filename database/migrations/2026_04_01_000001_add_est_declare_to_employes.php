<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employes', function (Blueprint $table) {
            $table->boolean('est_declare')->default(true)->after('statut');
        });

        Schema::table('fiches_paie', function (Blueprint $table) {
            $table->boolean('est_declare_snapshot')->default(true)->after('mode_remuneration_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('employes', function (Blueprint $table) {
            $table->dropColumn('est_declare');
        });

        Schema::table('fiches_paie', function (Blueprint $table) {
            $table->dropColumn('est_declare_snapshot');
        });
    }
};

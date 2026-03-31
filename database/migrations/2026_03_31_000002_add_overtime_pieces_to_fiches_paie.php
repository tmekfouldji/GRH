<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fiches_paie', function (Blueprint $table) {
            $table->decimal('montant_heures_supplementaires', 10, 2)->default(0)->after('heures_supplementaires');
            $table->unsignedInteger('pieces_fabriquees')->nullable()->after('prime_rendement');
            $table->decimal('prime_par_piece_snapshot', 10, 2)->nullable()->after('pieces_fabriquees');
            $table->enum('mode_remuneration_snapshot', ['salaire', 'piece'])->default('salaire')->after('prime_par_piece_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('fiches_paie', function (Blueprint $table) {
            $table->dropColumn([
                'montant_heures_supplementaires',
                'pieces_fabriquees',
                'prime_par_piece_snapshot',
                'mode_remuneration_snapshot',
            ]);
        });
    }
};

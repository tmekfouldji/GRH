<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employes', function (Blueprint $table) {
            $table->enum('mode_remuneration', ['salaire', 'piece'])->default('salaire')->after('mode_paiement');
            $table->decimal('prime_par_piece', 10, 2)->nullable()->after('mode_remuneration');
        });
    }

    public function down(): void
    {
        Schema::table('employes', function (Blueprint $table) {
            $table->dropColumn(['mode_remuneration', 'prime_par_piece']);
        });
    }
};

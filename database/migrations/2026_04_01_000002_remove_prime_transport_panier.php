<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employes', function (Blueprint $table) {
            $table->dropColumn(['prime_transport_defaut', 'prime_panier_defaut']);
        });

        Schema::table('fiches_paie', function (Blueprint $table) {
            $table->dropColumn('prime_transport');
        });
    }

    public function down(): void
    {
        Schema::table('employes', function (Blueprint $table) {
            $table->decimal('prime_transport_defaut', 10, 2)->default(0)->after('salaire_base');
            $table->decimal('prime_panier_defaut', 10, 2)->default(0)->after('prime_transport_defaut');
        });

        Schema::table('fiches_paie', function (Blueprint $table) {
            $table->decimal('prime_transport', 10, 2)->default(0)->after('prime_rendement');
        });
    }
};

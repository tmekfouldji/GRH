<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fiches_paie', function (Blueprint $table) {
            $table->decimal('jours_ponderes', 5, 2)->nullable()->after('jours_travailles');
        });
    }

    public function down(): void
    {
        Schema::table('fiches_paie', function (Blueprint $table) {
            $table->dropColumn('jours_ponderes');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('fiches_paie', function (Blueprint $table) {
            $table->decimal('deduction_retard', 10, 2)->default(0)->after('autres_deductions');
            $table->decimal('deduction_absence', 10, 2)->default(0)->after('deduction_retard');
            $table->integer('minutes_retard')->default(0)->after('deduction_absence');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fiches_paie', function (Blueprint $table) {
            $table->dropColumn(['deduction_retard', 'deduction_absence', 'minutes_retard']);
        });
    }
};

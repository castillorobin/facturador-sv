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
        Schema::table('dtes', function (Blueprint $table) {
           // 1 = Previo (Normal), 2 = Diferido (Contingencia)
        $table->integer('tipo_modelo')->default(1)->after('estado');
        
        // Aquí guardaremos el sello que nos de Hacienda al notificar el evento
        $table->string('sello_contingencia')->nullable()->after('tipo_modelo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dtes', function (Blueprint $table) {
            $table->dropColumn(['tipo_modelo', 'sello_contingencia']);
        });
    }
};

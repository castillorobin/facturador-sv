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
        Schema::create('nota_creditos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dte_id')->constrained('dtes'); // El CCF original
            $table->string('codigo_generacion')->unique();
            $table->string('numero_control');
            $table->decimal('monto_modificado', 10, 2);
            $table->text('motivo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nota_creditos');
    }
};

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
        Schema::create('dtes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies');
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('user_id')->constrained('users'); // Quién lo emitió
            
            // Datos MH
            $table->string('tipo_dte'); // 01=Factura, 03=CCF, etc.
            $table->string('codigo_generacion')->unique(); // UUID obligatorio
            $table->string('numero_control');
            $table->string('sello_recepcion')->nullable();
            $table->dateTime('fecha_emision');
            
            // Totales
            $table->decimal('monto_gravado', 12, 2)->default(0);
            $table->decimal('monto_exento', 12, 2)->default(0);
            $table->decimal('iva', 12, 2)->default(0);
            $table->decimal('total_pagar', 12, 2);
            
            // Archivos
            $table->string('ruta_json')->nullable();
            $table->string('ruta_pdf')->nullable();
            
            $table->enum('estado', ['BORRADOR', 'PROCESADO', 'RECHAZADO', 'ANULADO'])->default('BORRADOR');
            $table->text('observaciones_mh')->nullable(); // Por si hay errores de validación
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dtes');
    }
};

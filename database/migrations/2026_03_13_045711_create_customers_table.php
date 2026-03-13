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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            
            // Identificación Básica
            $table->string('nombre'); // Nombre o Razón Social
            $table->string('nombre_comercial')->nullable();
            $table->string('tipo_documento'); // 36=NIT, 13=DUI, 37=Pasaporte, etc.
            $table->string('num_documento'); // El número del documento seleccionado
            $table->string('nrc', 10)->nullable(); // Obligatorio para CCF
            
            // Actividad Económica (Para CCF)
            $table->string('cod_actividad', 5)->nullable();
            $table->string('desc_actividad')->nullable();
            
            // Ubicación
            $table->string('departamento', 2)->nullable();
            $table->string('municipio', 3)->nullable(); // Hacienda usa 3 dígitos para municipios a veces
            $table->text('direccion_complemento')->nullable();
            
            // Contacto
            $table->string('telefono', 20)->nullable();
            $table->string('email')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};

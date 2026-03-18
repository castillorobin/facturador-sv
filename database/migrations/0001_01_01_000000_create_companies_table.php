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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            // Datos Identificativos
            $table->string('nombre'); // Nombre o Razón Social
            $table->string('nombre_comercial');
            $table->string('nit', 14);
            $table->string('nrc', 10);
            
            // Actividad Económica
            $table->string('cod_actividad', 5); // Ej: 96092
            $table->string('desc_actividad');   // Ej: Servicios n.c.p.
            
            // Configuración de Establecimiento
            $table->string('tipo_establecimiento', 2)->default('02'); // 02 = Sucursal/Fijo
            $table->string('cod_estable_mh')->nullable();
            $table->string('cod_estable')->nullable();
            $table->string('cod_punto_venta_mh')->nullable();
            $table->string('cod_punto_venta')->nullable();
            
            // Ubicación (Códigos MH)
            $table->string('departamento', 2); // Ej: 02
            $table->string('municipio', 2);    // Ej: 01
            $table->text('direccion_complemento');
            
            // Contacto
            $table->string('telefono', 20);
            $table->string('email', 100);

            // Credenciales API (Lo que hablamos antes)
            $table->string('api_usuario');
            $table->string('api_password');
            $table->string('password_privado');
            $table->string('ambiente')->default('00');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};

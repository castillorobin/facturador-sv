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
            $table->string('sello_invalidacion')->nullable();
            $table->dateTime('fecha_invalidacion')->nullable();
            $table->text('motivo_invalidacion')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dtes', function (Blueprint $table) {
            //
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('viajes', function (Blueprint $table): void {
            $table->id(); $table->string('codigo', 9)->unique();
            $table->date('fecha_inicio')->nullable(); $table->date('fecha_fin')->nullable();
            $table->string('origen')->nullable(); $table->string('chofer')->nullable(); $table->string('acompanante')->nullable();
            $table->boolean('es_acompanante_chofer')->default(false); $table->string('camion')->nullable(); $table->string('patente')->nullable();
            $table->decimal('kilometros_recorridos', 12, 2)->nullable(); $table->decimal('velocidad_maxima', 8, 2)->nullable();
            $table->decimal('litros_ruta', 12, 2)->nullable(); $table->decimal('litros_san_luis', 12, 2)->nullable();
            $table->string('resultado_viaje')->nullable(); $table->text('observaciones')->nullable(); $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('viajes'); }
};

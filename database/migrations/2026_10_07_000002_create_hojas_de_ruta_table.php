<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hojas_de_ruta', function (Blueprint $table): void {
            $table->id(); $table->foreignId('viaje_id')->constrained('viajes')->cascadeOnDelete();
            $table->string('cliente'); $table->string('factura')->nullable()->unique(); $table->string('remito')->unique();
            $table->date('fecha_carga'); $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('hojas_de_ruta'); }
};

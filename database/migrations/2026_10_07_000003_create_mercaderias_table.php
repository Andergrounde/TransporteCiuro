<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mercaderias', function (Blueprint $table): void {
            $table->id(); $table->foreignId('hoja_de_ruta_id')->constrained('hojas_de_ruta')->cascadeOnDelete();
            $table->string('mercaderia'); $table->decimal('cantidad', 12, 3); $table->decimal('costo_unitario', 14, 2);
            $table->decimal('costo_sin_iva', 16, 2); $table->decimal('costo_con_iva', 16, 2);
            $table->string('estado', 40); $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('mercaderias'); }
};

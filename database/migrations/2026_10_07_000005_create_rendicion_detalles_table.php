<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rendicion_cobros', function (Blueprint $table): void {
            $table->id(); $table->foreignId('rendicion_id')->constrained('rendiciones')->cascadeOnDelete();
            $table->decimal('monto', 14, 2); $table->string('descripcion'); $table->timestamps();
        });
        Schema::create('rendicion_gastos', function (Blueprint $table): void {
            $table->id(); $table->foreignId('rendicion_id')->constrained('rendiciones')->cascadeOnDelete();
            $table->decimal('monto', 14, 2); $table->string('descripcion'); $table->timestamps();
        });
        Schema::create('cheques', function (Blueprint $table): void {
            $table->id(); $table->foreignId('rendicion_id')->constrained('rendiciones')->cascadeOnDelete();
            $table->decimal('monto', 14, 2); $table->string('banco'); $table->string('numero')->unique();
            $table->date('fecha_emision'); $table->string('cliente'); $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('cheques'); Schema::dropIfExists('rendicion_gastos'); Schema::dropIfExists('rendicion_cobros');
    }
};

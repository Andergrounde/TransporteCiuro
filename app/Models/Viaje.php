<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Viaje extends Model
{
    protected $table = 'viajes';
    protected $fillable = ['codigo', 'fecha_inicio', 'fecha_fin', 'origen', 'chofer', 'acompanante', 'es_acompanante_chofer', 'camion', 'patente', 'kilometros_recorridos', 'velocidad_maxima', 'litros_ruta', 'litros_san_luis', 'resultado_viaje', 'observaciones'];
    protected function casts(): array { return ['fecha_inicio' => 'date', 'fecha_fin' => 'date', 'es_acompanante_chofer' => 'boolean']; }

    protected static function booted(): void
    {
        static::creating(function (Viaje $viaje): void {
            if (filled($viaje->codigo)) return;
            do {
                $letters = '';
                for ($i = 0; $i < 3; $i++) $letters .= chr(random_int(65, 90));
                $code = $letters.str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            } while (static::where('codigo', $code)->exists());
            $viaje->codigo = $code;
        });
    }

    public function hojasDeRuta(): HasMany { return $this->hasMany(HojaDeRuta::class); }
    public function rendicion(): HasOne { return $this->hasOne(Rendicion::class); }
}

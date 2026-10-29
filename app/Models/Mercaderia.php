<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mercaderia extends Model
{
    public const ESTADOS = ['Pagó con efectivo', 'Pagó con transferencia', 'No pagó', 'Pagó con cheque', 'Pagó con tarjeta'];
    protected $table = 'mercaderias';
    protected $fillable = ['hoja_de_ruta_id', 'mercaderia', 'cantidad', 'costo_unitario', 'costo_sin_iva', 'costo_con_iva', 'estado'];
    protected function casts(): array { return ['cantidad' => 'decimal:3', 'costo_unitario' => 'decimal:2', 'costo_sin_iva' => 'decimal:2', 'costo_con_iva' => 'decimal:2']; }
    protected static function booted(): void
    {
        static::saving(function (Mercaderia $item): void {
            $sinIva = round((float) $item->cantidad * (float) $item->costo_unitario, 2);
            $item->costo_sin_iva = $sinIva;
            $item->costo_con_iva = round($sinIva * 1.21, 2);
        });
    }
    public function hojaDeRuta(): BelongsTo { return $this->belongsTo(HojaDeRuta::class); }
}

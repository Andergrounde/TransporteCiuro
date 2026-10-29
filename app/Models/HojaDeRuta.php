<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HojaDeRuta extends Model
{
    protected $table = 'hojas_de_ruta';
    protected $fillable = ['viaje_id', 'cliente', 'factura', 'remito', 'fecha_carga'];
    protected function casts(): array { return ['fecha_carga' => 'date']; }
    public function viaje(): BelongsTo { return $this->belongsTo(Viaje::class); }
    public function mercaderias(): HasMany { return $this->hasMany(Mercaderia::class); }
    protected function totalHojaSinIva(): Attribute { return Attribute::get(fn () => (float) $this->mercaderias()->sum('costo_sin_iva')); }
    protected function totalHojaConIva(): Attribute { return Attribute::get(fn () => (float) $this->mercaderias()->sum('costo_con_iva')); }
}

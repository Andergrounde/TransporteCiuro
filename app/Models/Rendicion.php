<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rendicion extends Model
{
    protected $table = 'rendiciones';
    protected $fillable = ['viaje_id'];
    public function viaje(): BelongsTo { return $this->belongsTo(Viaje::class); }
    public function cobros(): HasMany { return $this->hasMany(RendicionCobro::class); }
    public function gastos(): HasMany { return $this->hasMany(RendicionGasto::class); }
    public function cheques(): HasMany { return $this->hasMany(Cheque::class); }
    protected function totalCobrado(): Attribute { return Attribute::get(fn () => (float) $this->cobros()->sum('monto')); }
    protected function totalGastado(): Attribute { return Attribute::get(fn () => (float) $this->gastos()->sum('monto')); }
    protected function balance(): Attribute { return Attribute::get(fn () => $this->total_cobrado - $this->total_gastado); }
    protected function montoChofer(): Attribute
    {
        return Attribute::get(fn () => round($this->balance * ($this->viaje?->es_acompanante_chofer ? 0.10 : 0.13), 2));
    }
    protected function montoAcompanante(): Attribute
    {
        return Attribute::get(fn () => round($this->balance * ($this->viaje?->es_acompanante_chofer ? 0.10 : 0.07), 2));
    }
    protected function valorTotalCheques(): Attribute { return Attribute::get(fn () => (float) $this->cheques()->sum('monto')); }
}

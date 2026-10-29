<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RendicionGasto extends Model
{
    protected $table = 'rendicion_gastos';
    protected $fillable = ['rendicion_id', 'monto', 'descripcion'];
    public function rendicion(): BelongsTo { return $this->belongsTo(Rendicion::class); }
}

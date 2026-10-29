<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cheque extends Model
{
    protected $fillable = ['rendicion_id', 'monto', 'banco', 'numero', 'fecha_emision', 'cliente'];
    protected function casts(): array { return ['fecha_emision' => 'date', 'monto' => 'decimal:2']; }
    public function rendicion(): BelongsTo { return $this->belongsTo(Rendicion::class); }
}

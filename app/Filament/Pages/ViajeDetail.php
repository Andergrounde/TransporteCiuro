<?php

namespace App\Filament\Pages;

use App\Models\Cheque;
use App\Models\HojaDeRuta;
use App\Models\Mercaderia;
use App\Models\Rendicion;
use App\Models\Viaje;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ViajeDetail extends Page
{
    protected string $view = 'filament.pages.viaje-detail';
    protected static ?string $slug = 'viajes/{record}/detalle';
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $title = 'Hoja de ruta y rendición';

    public Viaje $viaje;
    public string $modo = 'ruta';
    public array $rutas = [];
    public array $cobros = [];
    public array $gastos = [];
    public array $cheques = [];
    public bool $editandoRutas = false;
    public bool $editandoRendicion = false;
    public bool $editandoCheques = false;

    public function mount(string $record): void
    {
        $this->viaje = Viaje::with(['hojasDeRuta.mercaderias', 'rendicion.cobros', 'rendicion.gastos', 'rendicion.cheques'])->findOrFail($record);
        $this->cargarEstado();
    }

    protected function cargarEstado(): void
    {
        $this->rutas = $this->viaje->hojasDeRuta->map(fn (HojaDeRuta $ruta) => [
            'id' => $ruta->id, 'cliente' => $ruta->cliente, 'factura' => $ruta->factura ?? '', 'remito' => $ruta->remito,
            'fecha_carga' => $ruta->fecha_carga?->format('Y-m-d'),
            'mercaderias' => $ruta->mercaderias->map(fn (Mercaderia $item) => [
                'id' => $item->id, 'cantidad' => $item->cantidad, 'mercaderia' => $item->mercaderia,
                'costo_unitario' => $item->costo_unitario, 'estado' => $item->estado,
            ])->values()->all(),
        ])->values()->all();
        $rendicion = $this->viaje->rendicion;
        $this->cobros = $rendicion?->cobros->map(fn ($r) => ['id' => $r->id, 'monto' => $r->monto, 'descripcion' => $r->descripcion])->values()->all() ?? [];
        $this->gastos = $rendicion?->gastos->map(fn ($r) => ['id' => $r->id, 'monto' => $r->monto, 'descripcion' => $r->descripcion])->values()->all() ?? [];
        $this->cheques = $rendicion?->cheques->map(fn ($c) => ['id' => $c->id, 'monto' => $c->monto, 'banco' => $c->banco, 'numero' => $c->numero, 'fecha_emision' => $c->fecha_emision->format('Y-m-d'), 'cliente' => $c->cliente])->values()->all() ?? [];
    }

    public function cambiarModo(string $modo): void { $this->modo = in_array($modo, ['ruta', 'rendicion'], true) ? $modo : 'ruta'; }
    public function agregarRuta(): void { $this->editandoRutas = true; $this->rutas[] = ['cliente' => '', 'factura' => '', 'remito' => '', 'fecha_carga' => now()->format('Y-m-d'), 'mercaderias' => []]; }
    public function agregarMercaderia(int $ruta): void { $this->editandoRutas = true; $this->rutas[$ruta]['mercaderias'][] = ['cantidad' => 1, 'mercaderia' => '', 'costo_unitario' => 0, 'estado' => 'No pagó']; }
    public function quitarRuta(int $index): void { unset($this->rutas[$index]); $this->rutas = array_values($this->rutas); }
    public function quitarMercaderia(int $ruta, int $item): void { unset($this->rutas[$ruta]['mercaderias'][$item]); $this->rutas[$ruta]['mercaderias'] = array_values($this->rutas[$ruta]['mercaderias']); }

    public function guardarRutas(): void
    {
        $this->validate([
            'rutas.*.cliente' => ['required', 'string', 'max:255'], 'rutas.*.factura' => ['nullable', 'string', 'max:255'],
            'rutas.*.remito' => ['required', 'string', 'max:255'], 'rutas.*.fecha_carga' => ['required', 'date'],
            'rutas.*.mercaderias.*.cantidad' => ['required', 'numeric', 'min:0'], 'rutas.*.mercaderias.*.mercaderia' => ['required', 'string', 'max:255'],
            'rutas.*.mercaderias.*.costo_unitario' => ['required', 'numeric', 'min:0'], 'rutas.*.mercaderias.*.estado' => ['required', Rule::in(Mercaderia::ESTADOS)],
        ]);
        DB::transaction(function (): void {
            $keepRoutes = [];
            foreach ($this->rutas as $data) {
                $ruta = isset($data['id']) ? $this->viaje->hojasDeRuta()->findOrFail($data['id']) : new HojaDeRuta();
                $ruta->fill(['cliente' => $data['cliente'], 'factura' => $data['factura'] ?: null, 'remito' => $data['remito'], 'fecha_carga' => $data['fecha_carga']]);
                $this->viaje->hojasDeRuta()->save($ruta);
                $keepRoutes[] = $ruta->id;
                $keepItems = [];
                foreach ($data['mercaderias'] ?? [] as $itemData) {
                    $item = isset($itemData['id']) ? $ruta->mercaderias()->findOrFail($itemData['id']) : new Mercaderia();
                    $item->fill(['cantidad' => $itemData['cantidad'], 'mercaderia' => $itemData['mercaderia'], 'costo_unitario' => $itemData['costo_unitario'], 'estado' => $itemData['estado']]);
                    $ruta->mercaderias()->save($item); $keepItems[] = $item->id;
                }
                $ruta->mercaderias()->when($keepItems, fn ($q) => $q->whereNotIn('id', $keepItems), fn ($q) => $q)->delete();
            }
            $this->viaje->hojasDeRuta()->when($keepRoutes, fn ($q) => $q->whereNotIn('id', $keepRoutes), fn ($q) => $q)->delete();
        });
        $this->viaje->refresh()->load('hojasDeRuta.mercaderias'); $this->cargarEstado();
        Notification::make()->title('Hoja de ruta guardada')->success()->send();
    }

    public function agregarCobro(): void { $this->editandoRendicion = true; $this->cobros[] = ['monto' => 0, 'descripcion' => '']; }
    public function agregarGasto(): void { $this->editandoRendicion = true; $this->gastos[] = ['monto' => 0, 'descripcion' => '']; }
    public function agregarCheque(): void { $this->editandoCheques = true; $this->cheques[] = ['monto' => 0, 'banco' => '', 'numero' => '', 'fecha_emision' => now()->format('Y-m-d'), 'cliente' => '']; }
    public function quitarCobro(int $index): void { unset($this->cobros[$index]); $this->cobros = array_values($this->cobros); }
    public function quitarGasto(int $index): void { unset($this->gastos[$index]); $this->gastos = array_values($this->gastos); }
    public function quitarCheque(int $index): void { unset($this->cheques[$index]); $this->cheques = array_values($this->cheques); }

    public function guardarRendicion(): void
    {
        $rules = [
            'cobros.*.monto' => ['required', 'numeric', 'min:0'], 'cobros.*.descripcion' => ['required', 'string', 'max:255'],
            'gastos.*.monto' => ['required', 'numeric', 'min:0'], 'gastos.*.descripcion' => ['required', 'string', 'max:255'],
            'cheques.*.monto' => ['required', 'numeric', 'min:0'], 'cheques.*.banco' => ['required', 'string', 'max:255'],
            'cheques.*.fecha_emision' => ['required', 'date'],
            'cheques.*.cliente' => ['required', 'string', 'max:255'],
        ];

        foreach ($this->cheques as $index => $cheque) {
            $numeroRules = ['required', 'string', 'max:255'];
            $uniqueNumero = Rule::unique('cheques', 'numero');

            if (filled($cheque['id'] ?? null)) {
                $uniqueNumero->ignore($cheque['id']);
            }

            $rules["cheques.{$index}.numero"] = [...$numeroRules, $uniqueNumero];
        }

        $this->validate($rules, [
            'cheques.*.numero.unique' => 'Ese número de cheque ya está registrado. Cada cheque debe tener un número único.',
        ]);

        // Catch duplicates among multiple unsaved rows before the database unique
        // constraint is reached, so the user receives a field-level message.
        $seenNumbers = [];
        foreach ($this->cheques as $index => $cheque) {
            $numero = trim((string) ($cheque['numero'] ?? ''));
            if ($numero === '') {
                continue;
            }

            if (isset($seenNumbers[$numero])) {
                $this->addError("cheques.{$index}.numero", 'Este número ya está repetido en otro cheque de esta rendición.');
                $this->addError("cheques.{$seenNumbers[$numero]}.numero", 'Este número ya está repetido en otro cheque de esta rendición.');
            } else {
                $seenNumbers[$numero] = $index;
            }
        }

        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }

        try {
            DB::transaction(function (): void {
                $rendicion = $this->viaje->rendicion()->firstOrCreate([]);
                foreach ([['cobros', $rendicion->cobros()], ['gastos', $rendicion->gastos()], ['cheques', $rendicion->cheques()]] as [$key, $relation]) {
                    $keep = [];
                    foreach ($this->{$key} as $row) {
                        // Resolve existing rows only inside their parent relation. A stale
                        // Livewire row id must not turn a save into a full-page 404.
                        $model = filled($row['id'] ?? null)
                            ? $relation->getRelated()->newQuery()
                                ->where($relation->getForeignKeyName(), $rendicion->getKey())
                                ->whereKey($row['id'])
                                ->first()
                            : null;
                        $model ??= $relation->getRelated()->newInstance();
                        $model->fill(collect($row)->except('id')->all()); $relation->save($model); $keep[] = $model->id;
                    }
                    $relation->when($keep, fn ($q) => $q->whereNotIn('id', $keep), fn ($q) => $q)->delete();
                }
            });
        } catch (UniqueConstraintViolationException) {
            $mensaje = 'Ese número de cheque ya está registrado. Ingresá un número distinto para cada cheque.';
            $indicesDuplicados = [];

            foreach ($this->cheques as $index => $cheque) {
                $numero = trim((string) ($cheque['numero'] ?? ''));
                if ($numero === '') {
                    continue;
                }

                $query = Cheque::query()->where('numero', $numero);
                if (filled($cheque['id'] ?? null)) {
                    $query->whereKeyNot($cheque['id']);
                }
                if ($query->exists()) {
                    $indicesDuplicados[] = $index;
                }
            }

            foreach ($indicesDuplicados as $index) {
                $this->addError("cheques.{$index}.numero", $mensaje);
            }

            if ($indicesDuplicados === []) {
                $this->addError('cheques', $mensaje);
            }

            Notification::make()
                ->title('No se pudo guardar el cheque')
                ->body($mensaje)
                ->danger()
                ->send();

            return;
        }

        $this->viaje->refresh()->load('rendicion.cobros', 'rendicion.gastos', 'rendicion.cheques'); $this->cargarEstado();
        Notification::make()->title('Rendición guardada')->success()->send();
    }

    protected function getViewData(): array
    {
        $totalCobrado = collect($this->cobros)->sum(fn ($row) => (float) ($row['monto'] ?? 0));
        $totalGastado = collect($this->gastos)->sum(fn ($row) => (float) ($row['monto'] ?? 0));
        $balance = $totalCobrado - $totalGastado;
        $porcentajeChofer = $this->viaje->es_acompanante_chofer ? 0.10 : 0.13;
        $porcentajeAcompanante = $this->viaje->es_acompanante_chofer ? 0.10 : 0.07;
        return ['totalCobrado' => $totalCobrado, 'totalGastado' => $totalGastado, 'balance' => $balance,
            'montoChofer' => round($balance * $porcentajeChofer, 2), 'montoAcompanante' => round($balance * $porcentajeAcompanante, 2),
            'totalCheques' => collect($this->cheques)->sum(fn ($row) => (float) ($row['monto'] ?? 0))];
    }
}

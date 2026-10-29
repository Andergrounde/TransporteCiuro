<x-filament-panels::page>
    <div class="ciuro-detail ciuro-detail--{{ $modo }}" x-data="{ confirmacionAbierta: false, confirmacionAccion: '', confirmacionArgs: [] }" @ciuro-confirm-delete.window="confirmacionAccion = $event.detail.action; confirmacionArgs = $event.detail.args; confirmacionAbierta = true">
        <div class="ciuro-detail__header">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Detalle del viaje · {{ $viaje->codigo }}</p>
                <h2 class="mt-1 text-xl font-semibold text-gray-950 dark:text-white">{{ $viaje->origen ?: 'Sin origen indicado' }}{{ $viaje->chofer ? ' · '.$viaje->chofer : '' }}</h2>
            </div>
            <div class="ciuro-mode-toggle" role="group" aria-label="Sección del viaje">
                <button wire:click="cambiarModo('ruta')" aria-pressed="{{ $modo === 'ruta' ? 'true' : 'false' }}" class="ciuro-mode-button ciuro-mode-button--ruta {{ $modo === 'ruta' ? 'is-active' : '' }}">
                    <x-filament::icon icon="heroicon-o-map" class="h-6 w-6" />
                    <span>HOJA DE RUTA</span>
                </button>
                <button wire:click="cambiarModo('rendicion')" aria-pressed="{{ $modo === 'rendicion' ? 'true' : 'false' }}" class="ciuro-mode-button ciuro-mode-button--rendicion {{ $modo === 'rendicion' ? 'is-active' : '' }}">
                    <x-filament::icon icon="heroicon-o-currency-dollar" class="h-6 w-6" />
                    <span>RENDICIÓN</span>
                </button>
            </div>
        </div>

        @if ($modo === 'ruta')
            <div class="ciuro-detail__actions ciuro-detail__actions--end">
                <x-filament::button wire:click="$toggle('editandoRutas')" color="gray" icon="heroicon-o-pencil">{{ $editandoRutas ? 'Bloquear edición' : 'EDITAR' }}</x-filament::button>
                <x-filament::button wire:click="agregarRuta" icon="heroicon-o-plus">Agregar registro de ruta</x-filament::button>
                <x-filament::button wire:click="guardarRutas" color="success" icon="heroicon-o-check">Guardar cambios</x-filament::button>
            </div>
            @forelse ($rutas as $i => $ruta)
                <section wire:key="ruta-{{ $ruta['id'] ?? 'nueva-'.$i }}" class="ciuro-detail__card ciuro-route-card p-5 sm:p-6">
                    <div class="flex items-center justify-between"><h3 class="text-lg font-semibold">Registro de ruta {{ $i + 1 }}</h3><x-filament::button size="lg" icon="heroicon-o-trash" color="danger" x-on:click="$dispatch('ciuro-confirm-delete', { action: 'quitarRuta', args: [{{ $i }}] })">Eliminar</x-filament::button></div>
                    <div class="ciuro-route-meta">
                        <label class="ciuro-field-label">Cliente<input wire:model="rutas.{{ $i }}.cliente" @disabled(! $editandoRutas) class="fi-input mt-1 w-full" /></label>
                        <label class="ciuro-field-label">Factura<input wire:model="rutas.{{ $i }}.factura" @disabled(! $editandoRutas) class="fi-input mt-1 w-full" /></label>
                        <label class="ciuro-field-label">Remito<input wire:model="rutas.{{ $i }}.remito" @disabled(! $editandoRutas) class="fi-input mt-1 w-full" /></label>
                        <label class="ciuro-field-label">Fecha de carga<input type="date" wire:model="rutas.{{ $i }}.fecha_carga" @disabled(! $editandoRutas) class="fi-input mt-1 w-full" /></label>
                    </div>
                    @error("rutas.$i.cliente") <p class="text-sm text-danger-600">{{ $message }}</p> @enderror
                    <div class="ciuro-road-table-wrap">
                        <table class="ciuro-road-table ciuro-detail__table text-left text-sm"><thead><tr class="border-b text-gray-500"><th>Cantidad</th><th>Mercancía</th><th>Costo unidad</th><th>Estado</th><th>Costo sin IVA</th><th>Costo con IVA</th><th></th></tr></thead>
                            <tbody>@foreach (($ruta['mercaderias'] ?? []) as $j => $item)
                                @php($subtotal = (float)($item['cantidad'] ?? 0) * (float)($item['costo_unitario'] ?? 0))
                                <tr class="border-b border-gray-100 dark:border-gray-800">
                                    <td class="p-2"><input type="number" min="0" step="0.001" wire:model.live="rutas.{{ $i }}.mercaderias.{{ $j }}.cantidad" @disabled(! $editandoRutas) class="w-24 rounded border-gray-300 dark:bg-gray-950" /></td>
                                    <td class="p-2"><input wire:model="rutas.{{ $i }}.mercaderias.{{ $j }}.mercaderia" @disabled(! $editandoRutas) class="min-w-36 rounded border-gray-300 dark:bg-gray-950" /></td>
                                    <td class="p-2"><input type="number" min="0" step="0.01" wire:model.live="rutas.{{ $i }}.mercaderias.{{ $j }}.costo_unitario" @disabled(! $editandoRutas) class="w-32 rounded border-gray-300 dark:bg-gray-950" /></td>
                                    <td class="p-2"><select wire:model="rutas.{{ $i }}.mercaderias.{{ $j }}.estado" @disabled(! $editandoRutas) class="rounded border-gray-300 dark:bg-gray-950">@foreach (\App\Models\Mercaderia::ESTADOS as $estado)<option>{{ $estado }}</option>@endforeach</select></td>
                                    <td class="p-2">${{ number_format($subtotal, 2, ',', '.') }}</td><td class="p-2">${{ number_format($subtotal * 1.21, 2, ',', '.') }}</td>
                                    <td><x-filament::icon-button icon="heroicon-o-x-mark" color="danger" label="Quitar mercancía" x-on:click="$dispatch('ciuro-confirm-delete', { action: 'quitarMercaderia', args: [{{ $i }}, {{ $j }}] })" /></td>
                                </tr>
                            @endforeach</tbody>
                        </table>
                    </div>
                    <x-filament::button wire:click="agregarMercaderia({{ $i }})" color="gray" icon="heroicon-o-plus" class="ciuro-add-merchandise">Agregar mercancía</x-filament::button>
                </section>
            @empty
                <div class="rounded-xl border border-dashed p-10 text-center text-gray-500">No hay registros de ruta. Usa “Agregar registro de ruta” para comenzar.</div>
            @endforelse
            @if (count($rutas))<div class="ciuro-detail__actions ciuro-detail__actions--end"><x-filament::button wire:click="guardarRutas" color="success">Guardar cambios</x-filament::button></div>@endif
        @else
            <div class="ciuro-detail__actions ciuro-detail__actions--end"><x-filament::button wire:click="$toggle('editandoRendicion')" color="gray" icon="heroicon-o-pencil">{{ $editandoRendicion ? 'Bloquear edición' : 'EDITAR' }}</x-filament::button><x-filament::button wire:click="guardarRendicion" color="success" icon="heroicon-o-check">Guardar cambios</x-filament::button></div>
            <section class="ciuro-detail__card p-5 sm:p-6">
                <div class="ciuro-billing-grid">
                    <div class="ciuro-entry-panel ciuro-entry-panel--cobros"><div class="ciuro-panel-heading"><h3 class="text-base font-bold text-blue-800 dark:text-blue-200">A COBRAR</h3><x-filament::button size="lg" icon="heroicon-o-plus" color="info" wire:click="agregarCobro">Agregar</x-filament::button></div>
                        @foreach ($cobros as $i => $row)<div class="ciuro-entry-row"><input type="number" min="0" step="0.01" wire:model.live="cobros.{{ $i }}.monto" placeholder="Monto" @disabled(! $editandoRendicion) class="w-full"/><input wire:model="cobros.{{ $i }}.descripcion" placeholder="Descripción" @disabled(! $editandoRendicion) class="w-full"/><x-filament::icon-button icon="heroicon-o-x-mark" color="danger" label="Quitar cobro" x-on:click="$dispatch('ciuro-confirm-delete', { action: 'quitarCobro', args: [{{ $i }}] })" /></div>@endforeach
                    </div>
                    <div class="ciuro-entry-panel ciuro-entry-panel--gastos"><div class="ciuro-panel-heading"><h3 class="text-base font-bold text-rose-800 dark:text-rose-200">GASTOS</h3><x-filament::button size="lg" icon="heroicon-o-plus" color="danger" wire:click="agregarGasto">Agregar</x-filament::button></div>
                        @foreach ($gastos as $i => $row)<div class="ciuro-entry-row"><input type="number" min="0" step="0.01" wire:model.live="gastos.{{ $i }}.monto" placeholder="Monto" @disabled(! $editandoRendicion) class="w-full"/><input wire:model="gastos.{{ $i }}.descripcion" placeholder="Descripción" @disabled(! $editandoRendicion) class="w-full"/><x-filament::icon-button icon="heroicon-o-x-mark" color="danger" label="Quitar gasto" x-on:click="$dispatch('ciuro-confirm-delete', { action: 'quitarGasto', args: [{{ $i }}] })" /></div>@endforeach
                    </div>
                </div>
                <div class="ciuro-summary-grid"><div class="ciuro-summary-item"><span>Total cobrado</span><strong>${{ number_format($totalCobrado, 2, ',', '.') }}</strong></div><div class="ciuro-summary-item"><span>Total gastado</span><strong>${{ number_format($totalGastado, 2, ',', '.') }}</strong></div><div class="ciuro-summary-item ciuro-summary-item--balance"><span>Balance</span><strong>${{ number_format($balance, 2, ',', '.') }}</strong></div></div>
                <div class="ciuro-compensation-summary grid gap-3 rounded-lg bg-gray-50 p-4 sm:grid-cols-2 dark:bg-gray-800"><div>Chofer ({{ $viaje->es_acompanante_chofer ? '10%' : '13%' }}): <strong>${{ number_format($montoChofer, 2, ',', '.') }}</strong></div><div>Acompañante ({{ $viaje->es_acompanante_chofer ? '10%' : '7%' }}): <strong>${{ number_format($montoAcompanante, 2, ',', '.') }}</strong></div></div>
            </section>

            <section class="ciuro-detail__card p-5 sm:p-6">
                <div class="ciuro-cheque-heading"><h3 class="text-lg font-bold">DETALLE DE CHEQUES</h3><div class="ciuro-detail__actions"><x-filament::button wire:click="$toggle('editandoCheques')" color="gray" icon="heroicon-o-pencil">{{ $editandoCheques ? 'Bloquear edición' : 'EDITAR' }}</x-filament::button><x-filament::button size="lg" icon="heroicon-o-plus" color="primary" wire:click="agregarCheque">Agregar cheque</x-filament::button></div></div>
                @error('cheques')<p class="rounded-lg border border-danger-200 bg-danger-50 px-4 py-3 text-sm font-medium text-danger-700 dark:border-danger-900 dark:bg-danger-950 dark:text-danger-200">{{ $message }}</p>@enderror
                <div class="ciuro-cheques-table-wrap"><table class="w-full text-left text-sm"><thead><tr class="border-b text-gray-500"><th class="p-2">Monto</th><th class="p-2">Banco</th><th class="p-2">N°</th><th class="p-2">Fecha emisión</th><th class="p-2">Cliente</th><th></th></tr></thead><tbody>
                    @foreach ($cheques as $i => $cheque)<tr class="border-b border-gray-100 dark:border-gray-800"><td class="p-2"><input type="number" min="0" step="0.01" wire:model.live="cheques.{{ $i }}.monto" @disabled(! $editandoCheques) class="w-28 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-950"/></td><td class="p-2"><input wire:model="cheques.{{ $i }}.banco" @disabled(! $editandoCheques) class="w-32 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-950"/></td><td class="p-2"><input wire:model="cheques.{{ $i }}.numero" @disabled(! $editandoCheques) class="w-28 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-950"/>@error("cheques.$i.numero")<p class="mt-1 text-xs text-danger-600">{{ $message }}</p>@enderror</td><td class="p-2"><input type="date" wire:model="cheques.{{ $i }}.fecha_emision" @disabled(! $editandoCheques) class="rounded border-gray-300 dark:border-gray-700 dark:bg-gray-950"/></td><td class="p-2"><input wire:model="cheques.{{ $i }}.cliente" @disabled(! $editandoCheques) class="w-32 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-950"/></td><td><x-filament::icon-button icon="heroicon-o-x-mark" color="danger" label="Quitar cheque" x-on:click="$dispatch('ciuro-confirm-delete', { action: 'quitarCheque', args: [{{ $i }}] })"/></td></tr>@endforeach
                </tbody></table></div>
                <div class="ciuro-cheques-total border-t pt-3 text-right">Valor total: <strong>${{ number_format($totalCheques, 2, ',', '.') }}</strong></div>
            </section>
            <div class="flex justify-end"><x-filament::button wire:click="guardarRendicion" color="success">Guardar cambios</x-filament::button></div>
        @endif

        <div x-cloak x-show="confirmacionAbierta" x-transition.opacity class="ciuro-confirm-overlay" role="presentation" @keydown.escape.window="confirmacionAbierta = false">
            <section x-show="confirmacionAbierta" x-transition class="ciuro-confirm-dialog" role="alertdialog" aria-modal="true" aria-labelledby="ciuro-confirm-title" @click.outside="confirmacionAbierta = false">
                <div class="ciuro-confirm-icon"><x-filament::icon icon="heroicon-o-trash" class="h-6 w-6" /></div>
                <h2 id="ciuro-confirm-title">¿Confirmás la eliminación?</h2>
                <p>El elemento seleccionado se quitará del formulario. Para guardar el cambio, presioná “Guardar cambios”.</p>
                <div class="ciuro-confirm-actions">
                    <x-filament::button color="gray" x-on:click="confirmacionAbierta = false">Cancelar</x-filament::button>
                    <x-filament::button color="danger" icon="heroicon-o-trash" x-on:click="const accion = confirmacionAccion; const argumentos = confirmacionArgs; confirmacionAbierta = false; $wire.call(accion, ...argumentos)">Eliminar</x-filament::button>
                </div>
            </section>
        </div>
    </div>
</x-filament-panels::page>

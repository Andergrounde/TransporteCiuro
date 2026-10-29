<?php

namespace App\Filament\Resources;

use BackedEnum;
use App\Filament\Pages\ViajeDetail;
use App\Filament\Resources\ViajeResource\Pages;
use App\Models\Viaje;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ViajeResource extends Resource
{
    protected static ?string $model = Viaje::class;
    protected static ?string $navigationLabel = 'Viajes';
    protected static ?string $modelLabel = 'viaje';
    protected static ?string $pluralModelLabel = 'viajes';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-truck';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Datos del viaje')->tabs([
                Tab::make('Recorrido y personal')->schema([
                    TextInput::make('codigo')->label('Código')->disabled()->dehydrated(false)->placeholder('Se genera automáticamente'),
                    DatePicker::make('fecha_inicio')->label('Fecha de inicio')->native(false),
                    DatePicker::make('fecha_fin')->label('Fecha de fin')->native(false),
                    TextInput::make('origen')->label('Origen')->maxLength(255),
                    TextInput::make('chofer')->label('Chofer')->maxLength(255),
                    TextInput::make('acompanante')->label('Acompañante')->maxLength(255),
                    Toggle::make('es_acompanante_chofer')->label('El acompañante también es chofer')->default(false),
                ])->columns(2),
                Tab::make('Camión y métricas')->schema([
                    TextInput::make('camion')->label('Camión')->maxLength(255),
                    TextInput::make('patente')->label('Patente')->maxLength(255),
                    TextInput::make('kilometros_recorridos')->label('Kilómetros recorridos')->numeric()->minValue(0),
                    TextInput::make('velocidad_maxima')->label('Velocidad máxima')->numeric()->minValue(0),
                    TextInput::make('litros_ruta')->label('Litros en ruta')->numeric()->minValue(0),
                    TextInput::make('litros_san_luis')->label('Litros en San Luis')->numeric()->minValue(0),
                ])->columns(2),
                Tab::make('Cierre')->schema([
                    TextInput::make('resultado_viaje')->label('Resultado del viaje')->maxLength(255),
                    Textarea::make('observaciones')->label('Observaciones')->rows(5),
                ])->columns(2),
            ])->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('fecha_inicio', 'desc')->columns([
            TextColumn::make('codigo')->label('COD')->searchable()->sortable(),
            TextColumn::make('fecha_inicio')->label('Fecha de inicio')->date('d/m/Y')->sortable(),
            TextColumn::make('origen')->label('Origen')->searchable()->placeholder('—'),
            TextColumn::make('chofer')->label('Chofer')->searchable()->placeholder('—'),
            TextColumn::make('acompanante')->label('Acompañante')->searchable()->placeholder('—'),
        ])->actions([
            EditAction::make()->label('Editar')->modalHeading('Editar viaje')->modalWidth('7xl'),
            Action::make('detalle')->label('Hoja de ruta y rendición')->icon('heroicon-o-document-text')
                ->url(fn (Viaje $record): string => ViajeDetail::getUrl(['record' => $record->getKey()])),
        ])->emptyStateHeading('Todavía no hay viajes')->emptyStateDescription('Crea el primer viaje para empezar a gestionar las rutas y rendiciones.');
    }

    public static function getEloquentQuery(): Builder { return parent::getEloquentQuery()->latest('fecha_inicio'); }
    public static function getPages(): array { return ['index' => Pages\ListViajes::route('/')]; }
}

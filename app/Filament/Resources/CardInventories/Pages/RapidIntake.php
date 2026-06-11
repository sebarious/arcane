<?php

namespace App\Filament\Resources\CardInventories\Pages;

use App\Filament\Resources\CardInventories\CardInventoryResource;
use App\Models\Card;
use App\Support\Money;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;

class RapidIntake extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = CardInventoryResource::class;

    protected string $view = 'filament.resources.card-inventories.pages.rapid-intake';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'acquired_at'     => now()->toDateString(),
            'acquired_from'   => '',
            'acquisition_lot' => 'LOT-'.now()->format('Y-md').'-'.strtoupper(\Illuminate\Support\Str::random(4)),
            'rows'            => [['card_id' => null, 'cost_pounds' => null, 'quantity' => 1]],
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Acquisition details')
                    ->description('These apply to every card you add below.')
                    ->columns(3)
                    ->schema([
                        DatePicker::make('acquired_at')->required(),
                        TextInput::make('acquired_from')
                            ->label('Source')
                            ->placeholder('e.g. Cardiff Card Show 2026-06'),
                        TextInput::make('acquisition_lot')
                            ->label('Lot reference')
                            ->required(),
                    ]),

                Section::make('Cards')
                    ->schema([
                        Repeater::make('rows')
                            ->label('')
                            ->addActionLabel('+ Add another card')
                            ->defaultItems(1)
                            ->columns(12)
                            ->schema([
                                Select::make('card_id')
                                    ->label('Card')
                                    ->columnSpan(7)
                                    ->searchable(['name', 'set_name', 'card_number'])
                                    ->getSearchResultsUsing(fn (string $search) =>
                                        Card::query()
                                            ->where(function ($q) use ($search) {
                                                $q->where('name', 'ilike', "%{$search}%")
                                                  ->orWhere('card_number', 'ilike', "%{$search}%");
                                            })
                                            ->limit(20)
                                            ->get()
                                            ->mapWithKeys(fn ($c) => [
                                                $c->id => "{$c->name} · {$c->set_name} · {$c->card_number}"
                                                    .($c->variant && $c->variant !== 'standard' ? " ({$c->variant})" : '')
                                                    .' — '.Money::format($c->current_market_pence ?? 0),
                                            ]))
                                    ->getOptionLabelUsing(fn ($value) => Card::find($value)?->name)
                                    ->required(),

                                TextInput::make('cost_pounds')
                                    ->label('Cost (£)')
                                    ->columnSpan(3)
                                    ->numeric()
                                    ->step(0.01)
                                    ->prefix('£')
                                    ->required(),

                                TextInput::make('quantity')
                                    ->label('Qty')
                                    ->columnSpan(2)
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(1)
                                    ->required(),
                            ])
                            ->itemLabel(fn (array $state) =>
                                $state['card_id']
                                    ? Card::find($state['card_id'])?->name
                                    : null),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        $created = 0;
        DB::transaction(function () use ($state, &$created) {
            foreach ($state['rows'] as $row) {
                $card = Card::find($row['card_id']);
                if (! $card) continue;

                $inventory = $card->addToInventory(
                    costPence:      Money::toPence($row['cost_pounds']),
                    acquiredAt:     $state['acquired_at'],
                    acquiredFrom:   $state['acquired_from'] ?: null,
                    acquisitionLot: $state['acquisition_lot'] ?: null,
                    quantity:       (int) ($row['quantity'] ?? 1),
                );
                $created += $inventory->count();
            }
        });

        Notification::make()
            ->title("Intake complete")
            ->body("{$created} cards added to inventory.")
            ->success()
            ->send();

        $this->redirect(CardInventoryResource::getUrl('index'));
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Save intake')
                ->action('save')
                ->keyBindings(['mod+s']),
        ];
    }
}

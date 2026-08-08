<?php

namespace App\Filament\Pages;

use App\Models\ApiSetting;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ApiSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static string|UnitEnum|null $navigationGroup = 'Sellers';

    protected static ?string $navigationLabel = 'API Settings';

    protected static ?string $title = 'API Settings';

    protected string $view = 'filament.pages.api-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(ApiSetting::current()->only(['rate_limit_per_minute']));
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->description(
                        'Applies to every store with API access — see the "Allow this seller to use '
                        .'the API" toggle on each store. Daily request limits are set per store instead, '
                        .'on the store\'s own edit page.'
                    )
                    ->schema([
                        TextInput::make('rate_limit_per_minute')
                            ->label('Rate limit (requests per minute)')
                            ->numeric()
                            ->minValue(1)
                            ->required(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        ApiSetting::current()->update([
            'rate_limit_per_minute' => (int) $state['rate_limit_per_minute'],
        ]);

        Notification::make()
            ->title('API settings updated')
            ->success()
            ->send();
    }
}

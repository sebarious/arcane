<?php

namespace App\Filament\Pages;

use App\Enums\BatchType;
use App\Models\BatchTypeSetting;
use BackedEnum;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class BatchTierSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static string|UnitEnum|null $navigationGroup = 'Batches & billing';

    protected static ?string $navigationLabel = 'Batch Tiers';

    protected static ?string $title = 'Batch Tiers';

    protected string $view = 'filament.pages.batch-tier-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(BatchTypeSetting::enabledMap());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->description(
                        'Turn a tier off to stop sellers requesting it — it still shows on the '
                        .'request form, just disabled. Existing batches of that type are unaffected.'
                    )
                    ->schema(collect(BatchType::cases())
                        ->map(fn (BatchType $type) => Toggle::make($type->value)->label($type->label()))
                        ->all()),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        foreach (BatchType::cases() as $type) {
            BatchTypeSetting::query()
                ->where('type', $type->value)
                ->update(['enabled' => (bool) ($state[$type->value] ?? true)]);
        }

        Notification::make()
            ->title('Batch tiers updated')
            ->success()
            ->send();
    }
}

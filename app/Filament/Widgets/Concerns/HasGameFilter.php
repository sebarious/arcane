<?php

namespace App\Filament\Widgets\Concerns;

use App\Enums\Game;
use Filament\Schemas\Schema;
use Filament\Forms;

trait HasGameFilter
{
  public ?string $gameFilter = null;

  public function filtersForm(Schema $form): Schema
  {
    return $form->components([
      Forms\Components\Select::make('gameFilter')
        ->label('Game')
        ->placeholder('All games')
        ->options(collect(Game::cases())->mapWithKeys(
          fn(Game $g) => [$g->value => $g->label()]
        ))
        ->live(),
    ]);
  }

  protected function applyGameFilter($query, string $column = 'game')
  {
    if ($this->gameFilter) {
      $query->where($column, $this->gameFilter);
    }
    return $query;
  }
}

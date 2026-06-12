<?php

namespace App\Enums;

enum Game: string
{
  case Pokemon  = 'pokemon';
  case Magic    = 'mtg';
  case Lorcana  = 'lorcana';
  case OnePiece = 'onepiece';

  public function label(): string
  {
    return match ($this) {
      self::Pokemon  => 'Pokémon',
      self::Magic    => 'Magic: The Gathering',
      self::Lorcana  => 'Lorcana',
      self::OnePiece => 'One Piece',
    };
  }
}

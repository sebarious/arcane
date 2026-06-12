<?php

namespace App\Enums;

enum BatchType: string
{
  case Sapphire = 'sapphire';
  case Ruby     = 'ruby';
  case Diamond  = 'diamond';

  protected function config(): array
  {
    return config("batches.types.{$this->value}") ?? [];
  }

  public function packCount(): int
  {
    return (int) ($this->config()['packs'] ?? 0);
  }

  /** Ex-VAT per-pack price in pence. */
  public function pricePerPackPence(): int
  {
    return (int) ($this->config()['price_per_pack_pence'] ?? 0);
  }

  /** Ex-VAT sale price per batch in pence. */
  public function batchSalePricePence(): int
  {
    return $this->packCount() * $this->pricePerPackPence();
  }

  public function label(): string
  {
    return $this->config()['label'] ?? ucfirst($this->value);
  }
}

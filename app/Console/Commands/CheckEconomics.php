<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('arcane:check-economics')]
#[Description('Check the economics of card inventory')]
class CheckEconomics extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $e = \App\Models\CardInventory::query()
            ->inStock()
            ->selectRaw('rarity_band, count(*) as n,
                 round(avg(cost_pence)::numeric / 100, 2) as avg_cost,
                 round(avg(market_value_pence)::numeric / 100, 2) as avg_market,
                 round(sum(cost_pence)::numeric / 100, 2) as total_cost,
                 round(sum(market_value_pence)::numeric / 100, 2) as total_market')
            ->groupBy('rarity_band')
            ->get()
            ->toArray();

        $this->table(
            ['Rarity Band', 'Count', 'Avg Cost (£)', 'Avg Market (£)', 'Total Cost (£)', 'Total Market (£)'],
            $e
        );

        $totalCost   = array_sum(array_column($e, 'total_cost'));
        $totalMarket = array_sum(array_column($e, 'total_market'));
        $this->info("Total Cost: £{$totalCost}");
        $this->info("Total Market: £{$totalMarket}");

        return self::SUCCESS;
    }
}

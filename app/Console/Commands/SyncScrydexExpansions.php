<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('arcane:sync-scrydex-expansions {--game=pokemon : The game to sync expansions for (default: pokemon)}')]
#[Description('Command description')]
class SyncScrydexExpansions extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $game = $this->option('game') ?? 'pokemon';

        $config = config('services.scrydex');

        $client = new \App\Services\Scrydex\ScrydexClient(
            baseUrl: $config['url'],
            apiKey: $config['key'],
            teamId: $config['team_id'],
        );

        $expansions = $client->listExpansions(null, $game);

        // UPSERT expansions into the database
        foreach ($expansions as $expansion) {
            \App\Models\Expansion::updateOrCreate(
                ['scrydex_id' => $expansion['id'], 'game' => $game],
                [
                    'name' => $expansion['name'] ?? null,
                    'series' => $expansion['series'] ?? null,
                    'code' => $expansion['code'] ?? null,
                    'total' => $expansion['total'] ?? null,
                    'printed_total' => $expansion['printed_total'] ?? null,
                    'language' => $expansion['language'] ?? null,
                    'language_code' => $expansion['language_code'] ?? null,
                    'release_date' => $expansion['release_date'] ?? null,
                    'is_online_only' => $expansion['is_online_only'] ?? false,
                    'logo' => $expansion['logo'] ?? null,
                    'symbol' => $expansion['symbol'] ?? null,
                    'translations' => $expansion['translations'] ?? null,
                ]
            );
        }

        $this->info('Scrydex expansions synced successfully.');

        return Command::SUCCESS;
    }
}

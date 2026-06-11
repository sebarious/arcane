<?php

namespace App\Console\Commands;

use App\Services\Scrydex\CardMapper;
use App\Services\Scrydex\ScrydexClient;
use Illuminate\Console\Command;

class ImportScrydexExpansionCommand extends Command
{
    protected $signature = 'arcane:import-expansion
                            {expansion? : Expansion ID, e.g. sv3pt5}
                            {--language=en : en or ja}
                            {--list : List recent expansions and exit}';

    protected $description = 'Import a Pokémon expansion from Scrydex into the card library';

    public function handle(ScrydexClient $client): int
    {
        $language = $this->option('language');

        if ($this->option('list')) {
            $expansions = $client->listExpansions($language);
            $this->table(
                ['ID', 'Name', 'Series', 'Released', 'Total'],
                collect($expansions)->map(fn ($e) => [
                    $e['id'] ?? '',
                    $e['name'] ?? '',
                    $e['series'] ?? '',
                    $e['release_date'] ?? '',
                    $e['total'] ?? '',
                ])->take(40)->toArray(),
            );
            $this->info('Showing 40 most recent. Use the ID with: arcane:import-expansion <id>');
            return self::SUCCESS;
        }

        $expansionId = $this->argument('expansion') ?? $this->ask('Expansion ID (e.g. sv3pt5)');

        $this->info("Fetching cards for {$expansionId}…");
        $cards = $client->getCardsInExpansion($expansionId, $language);

        if (empty($cards)) {
            $this->warn("No cards found for {$expansionId}.");
            return self::SUCCESS;
        }

        $this->info("Importing ".count($cards)." cards…");
        $bar = $this->output->createProgressBar(count($cards));

        foreach ($cards as $payload) {
            CardMapper::upsert($payload);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Done. {$expansionId} → ".count($cards)." cards imported with prices.");

        return self::SUCCESS;
    }
}

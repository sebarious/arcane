<?php

namespace App\Enums;

/**
 * Whether a Digital Rip pack may draw graded slabs.
 *
 * A rip sells the same promise as a sealed pack, so it draws from the same
 * quality pool: anything flagged not_for_batches is held back from both.
 * Graded cards are the single exception — they can never go into a batch
 * (CardInventory::scopeBatchable()), but a rip pack may well want them, so
 * each pack decides for itself.
 */
enum RipGradedPolicy: string
{
    /** Batch-quality raw cards only — no slabs. The default. */
    case Exclude = 'exclude';

    /** Raw batch-quality cards and graded slabs drawn from one shared pool. */
    case Allow = 'allow';

    /** Slabs only — a graded-exclusive pack. */
    case Only = 'only';

    public function label(): string
    {
        return match ($this) {
            self::Exclude => 'No graded cards',
            self::Allow => 'Allow graded cards',
            self::Only => 'Only graded cards',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Exclude => 'Draws batch-quality raw cards only.',
            self::Allow => 'Draws raw cards and graded slabs from one pool.',
            self::Only => 'Draws graded slabs exclusively.',
        };
    }

    /** @return array<string, string> value => label, for a Filament select. */
    public static function options(): array
    {
        return array_reduce(
            self::cases(),
            fn (array $carry, self $case) => $carry + [$case->value => $case->label()],
            [],
        );
    }
}

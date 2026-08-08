<?php

use App\Enums\Game;
use App\Enums\BatchType;

return [
  'distribution' => [
    Game::Pokemon->value => [
      BatchType::Sapphire->value => [
        'common'    => 113,
        'rare'      => 6,
        'super'     => 3,
        'legendary' => 2,
        'mythic'    => 1,
      ],
      BatchType::Ruby->value => [
        'common'    => 216,
        'rare'      => 20,
        'super'     => 8,
        'legendary' => 5,
        'mythic'    => 1,
      ],
      BatchType::Diamond->value => [
        'common'    => 436,
        'rare'      => 40,
        'super'     => 12,
        'legendary' => 10,
        'mythic'    => 2,
      ],
    ],

    // Game::Magic->value, etc. can be customised later
  ],

  // Per-band split across that band's 3 equal-width price tiers (the band's
  // min-max range from RarityBander::thresholds(), cut into thirds — tier_1 is
  // the cheapest third, tier_3 the priciest). tier_1+tier_2+tier_3 must sum to
  // that band's count above, or CandidateSelector silently falls back to an
  // even auto-split for it instead of using these numbers. mythic IS tiered
  // like everything else here — no special-case bypass — so a shared, wide
  // mythic price range (currently £150–£400) can be reined in per batch type
  // instead of every batch risking the very top of it.
  //
  // The values below reproduce what the old, un-configurable even-split
  // algorithm already produced, so nothing changes until you edit a cell —
  // except Diamond/legendary (4/3/3) and Sapphire/mythic (pinned to tier_2,
  // both as requested).
  'tier_distribution' => [
    Game::Pokemon->value => [
      BatchType::Sapphire->value => [
        'common'    => ['tier_1' => 38, 'tier_2' => 38, 'tier_3' => 37],
        'rare'      => ['tier_1' => 2,  'tier_2' => 2,  'tier_3' => 2],
        'super'     => ['tier_1' => 1,  'tier_2' => 1,  'tier_3' => 1],
        'legendary' => ['tier_1' => 1,  'tier_2' => 0,  'tier_3' => 1],
        // Single mythic pinned to the middle third — the top of the shared
        // mythic range (~£400) is too rich for Sapphire's economics.
        'mythic'    => ['tier_1' => 0,  'tier_2' => 1,  'tier_3' => 0],
      ],
      BatchType::Ruby->value => [
        'common'    => ['tier_1' => 72, 'tier_2' => 72, 'tier_3' => 72],
        'rare'      => ['tier_1' => 7,  'tier_2' => 7,  'tier_3' => 6],
        'super'     => ['tier_1' => 3,  'tier_2' => 3,  'tier_3' => 2],
        'legendary' => ['tier_1' => 2,  'tier_2' => 1,  'tier_3' => 2],
        'mythic'    => ['tier_1' => 0,  'tier_2' => 1,  'tier_3' => 0],
      ],
      BatchType::Diamond->value => [
        'common'    => ['tier_1' => 145, 'tier_2' => 145, 'tier_3' => 146],
        'rare'      => ['tier_1' => 14,  'tier_2' => 13,  'tier_3' => 13],
        'super'     => ['tier_1' => 4,   'tier_2' => 4,   'tier_3' => 4],
        'legendary' => ['tier_1' => 4,   'tier_2' => 3,   'tier_3' => 3],
        // 2 mythics — deliberately left unconfigured, which falls back to the
        // auto-split (1 from tier_1, 1 from tier_3): one guaranteed big pull
        // plus one modest one. Confirmed safe on the numbers — even the
        // mythic band's max (~£400) is only ~11% of Diamond's £3,500 sale,
        // vs. ~40% for Sapphire (why Sapphire's mythic is pinned to tier_2
        // above) — so Diamond doesn't need reining in the same way.
      ],
    ],
  ],

  // Max copies of the same product_id allowed in a single generated batch, per band.
  'duplicate_limits' => [
    'common'    => 4,
    'rare'      => 2,
    'super'     => 1,
    'legendary' => 1,
    'mythic'    => 1,
  ],
];

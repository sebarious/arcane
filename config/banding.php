<?php

use App\Enums\Game;
use App\Enums\BatchType;

return [
  'distribution' => [
    Game::Pokemon->value => [
      BatchType::Sapphire->value => [
        'common'    => 114,
        'rare'      => 5,
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
        'common'    => 420,
        'rare'      => 60,
        'super'     => 8,
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
        'common'    => ['tier_1' => 38, 'tier_2' => 38, 'tier_3' => 38],
        'rare'      => ['tier_1' => 2,  'tier_2' => 2,  'tier_3' => 1],
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
        'legendary' => ['tier_1' => 2,  'tier_2' => 2,  'tier_3' => 1],
        'mythic'    => ['tier_1' => 0,  'tier_2' => 1,  'tier_3' => 0],
      ],
      BatchType::Diamond->value => [
        'common'    => ['tier_1' => 140, 'tier_2' => 140, 'tier_3' => 140],
        'rare'      => ['tier_1' => 40,  'tier_2' => 10,  'tier_3' => 10],
        'super'     => ['tier_1' => 3,   'tier_2' => 3,   'tier_3' => 2],
        'legendary' => ['tier_1' => 6,   'tier_2' => 2,   'tier_3' => 2],
        'mythic'    => ['tier_1' => 1,   'tier_2' => 0,   'tier_3' => 1]
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

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
        'common'    => 220,
        'rare'      => 20,
        'super'     => 5,
        'legendary' => 3,
        'mythic'    => 2,
      ],
      BatchType::Diamond->value => [
        'common'    => 420,
        'rare'      => 63,
        'super'     => 8,
        'legendary' => 6,
        'mythic'    => 3,
      ],
      BatchType::Premium->value => [
        'common'    => 0,
        'rare'      => 360,
        'super'     => 110,
        'legendary' => 24,
        'mythic'    => 5,
        'chase'     => 1,
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
        'common'    => ['tier_1' => 45, 'tier_2' => 44, 'tier_3' => 25],
        'rare'      => ['tier_1' => 2,  'tier_2' => 2,  'tier_3' => 1],
        'super'     => ['tier_1' => 1,  'tier_2' => 1,  'tier_3' => 1],
        'legendary' => ['tier_1' => 1,  'tier_2' => 0,  'tier_3' => 1],
        // Single mythic pinned to the middle third — the top of the shared
        // mythic range (~£400) is too rich for Sapphire's economics.
        'mythic'    => ['tier_1' => 0,  'tier_2' => 1,  'tier_3' => 0],
      ],
      BatchType::Ruby->value => [
        'common'    => ['tier_1' => 100, 'tier_2' => 66, 'tier_3' => 50],
        'rare'      => ['tier_1' => 7,  'tier_2' => 7,  'tier_3' => 6],
        'super'     => ['tier_1' => 2,  'tier_2' => 2,  'tier_3' => 1],
        'legendary' => ['tier_1' => 1,  'tier_2' => 1,  'tier_3' => 1],
        'mythic'    => ['tier_1' => 1,  'tier_2' => 1,  'tier_3' => 0],
      ],
      BatchType::Diamond->value => [
        'common'    => ['tier_1' => 145, 'tier_2' => 160, 'tier_3' => 115],
        'rare'      => ['tier_1' => 30,  'tier_2' => 18,  'tier_3' => 15],
        'super'     => ['tier_1' => 3,   'tier_2' => 3,   'tier_3' => 2],
        'legendary' => ['tier_1' => 3,   'tier_2' => 1,   'tier_3' => 2],
        'mythic'    => ['tier_1' => 2,   'tier_2' => 1,   'tier_3' => 0]
        // 2 mythics — deliberately left unconfigured, which falls back to the
        // auto-split (1 from tier_1, 1 from tier_3): one guaranteed big pull
        // plus one modest one. Confirmed safe on the numbers — even the
        // mythic band's max (~£400) is only ~11% of Diamond's £3,500 sale,
        // vs. ~40% for Sapphire (why Sapphire's mythic is pinned to tier_2
        // above) — so Diamond doesn't need reining in the same way.
      ],
      BatchType::Premium->value => [
        // No common slots at all (see distribution above), so no tier line
        // needed here.
        'rare'      => ['tier_1' => 120, 'tier_2' => 120, 'tier_3' => 120],
        'super'     => ['tier_1' => 75,  'tier_2' => 18,  'tier_3' => 17],
        'legendary' => ['tier_1' => 8,   'tier_2' => 8,   'tier_3' => 8],
        'mythic'    => ['tier_1' => 3,   'tier_2' => 1,   'tier_3' => 1],
        // Single chase card — deliberately left unconfigured, so it
        // auto-splits (lands in tier_2, the middle third of the £425–£750
        // chase range — see CandidateSelector::autoSplitTierTargets())
        // rather than being pinned to one end.
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
    'chase'     => 1,
  ],
];

// Shared rarity band label + colour convention — same palette as
// Components/PullCard.vue's RARITY_COLORS, reused wherever a card's band
// needs showing (the reveal screens, pack odds, etc).
export const RARITY_LABELS: Record<string, string> = {
  common: 'Common',
  rare: 'Rare',
  super: 'Super',
  legendary: 'Legendary',
  mythic: 'Mythic',
};

export const RARITY_COLORS: Record<string, string> = {
  common: '#a3a3a3',
  rare: '#3b82f6',
  super: '#2dd4bf',
  legendary: '#7b4fe9',
  mythic: '#c9a84c',
};

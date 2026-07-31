export function cn ( ...values: Array<string | false | null | undefined> ) {
  return values.filter( Boolean ).join( ' ' );
}

import type { Pull } from '../types';

export const RARITY_COLORS: Record<
  Pull['card']['band'],
  { glow: string; shimmer: string; badge: string; }
> = {
  common: {
    glow: 'rgba(163,163,163,0.65)',
    shimmer: 'rgba(163,163,163,0.1)',
    badge: '#a3a3a3',
  },
  rare: {
    glow: 'rgba(59,130,246,0.75)',
    shimmer: 'rgba(59,130,246,0.1)',
    badge: '#3b82f6',
  },
  super: {
    glow: 'rgba(45,212,191,0.75)',
    shimmer: 'rgba(45,212,191,0.1)',
    badge: '#2dd4bf',
  },
  legendary: {
    glow: 'rgba(123,79,233,0.65)',
    shimmer: 'rgba(123,79,233,0.1)',
    badge: '#7b4fe9',
  },
  mythic: {
    glow: 'rgba(201,168,76,0.75)',
    shimmer: 'rgba(201, 168, 76, 0.2)',
    badge: '#c9a84c',
  },
};
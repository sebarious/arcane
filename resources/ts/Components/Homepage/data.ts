// src/data.ts
import arcanePack from '@/Assets/Arcane_pack.png';
import arcaneLogo from '@/Assets/Link___Arcane.png';
import imgVictini from '@/Assets/svp-208.png';
import imgBubbleMew from '@/Assets/sv4pt5-232.png';
import imgPikachu from '@/Assets/Pikachu.png';
import imgLugia from '@/Assets/Lugia.png';
import imgMew from '@/Assets/Mew.png';
import imgRayquaza from '@/Assets/Rayquaza.png';

export const pulls = [
  { name: 'Charizard ex', set: 'Obsidian Flames', grade: 'PSA 9', rarity: 'Ultra', value: '£85', img: imgVictini },
  { name: 'Umbreon VMAX', set: 'Evolving Skies', grade: 'PSA 10', rarity: 'Legendary', value: '£350', img: imgBubbleMew },
  { name: 'Pikachu VMAX', set: 'Vivid Voltage', grade: 'PSA 9', rarity: 'Rare', value: '£200', img: imgPikachu },
  { name: 'Lugia V', set: 'Silver Tempest', grade: 'PSA 10', rarity: 'Legendary', value: '£180', img: imgLugia },
  { name: 'Mew VMAX', set: 'Fusion Strike', grade: 'PSA 9', rarity: 'Common', value: '£95', img: imgMew },
  { name: 'Rayquaza VMAX', set: 'Evolving Skies', grade: 'PSA 9', rarity: 'God', value: '£420', img: imgRayquaza },
];

export const livePool = [
  { name: 'Charizard V', set: 'Brilliant Stars', grade: 'PSA 9', qty: 1, streamer: '@FlameKing_TV', img: imgVictini },
  { name: 'Mewtwo ex', set: 'SV 151', grade: 'PSA 9', qty: 2, streamer: '@PsychicPulls', img: imgLugia },
  { name: 'Gardevoir ex', set: 'Scarlet & Violet', grade: 'PSA 9', qty: 1, streamer: '@GardeStream', img: imgBubbleMew },
  { name: 'Miraidon ex', set: 'Scarlet & Violet', grade: 'PSA 10', qty: 1, streamer: '@VoltBreaker', img: imgPikachu },
  { name: 'Iron Valiant ex', set: 'Paradox Rift', grade: 'PSA 9', qty: 3, streamer: '@IronPackTV', img: imgRayquaza },
  { name: 'Iono Full Art', set: 'Paldea Evolved', grade: 'PSA 9', qty: 1, streamer: '@IonoStation', img: imgMew },
  { name: 'Pidgeot ex', set: 'Obsidian Flames', grade: 'PSA 9', qty: 2, streamer: '@BirdCatcher99', img: imgVictini },
  { name: 'Roaring Moon ex', set: 'Paradox Rift', grade: 'PSA 10', qty: 1, streamer: '@MoonPulls', img: imgRayquaza },
];

// src/data.ts
import { Shield, Package, Eye } from 'lucide-vue-next';
import type { Step } from '../../types'; // optional, if you created types.ts
export const steps: Step[] = [
  {
    num: '01',
    title: 'Packed & Sealed by Arcane.',
    desc: 'Every mystery pack is assembled, quality checked and sealed by Arcane before it’s dispatched.\n\nStreamers never handle or preview the contents beforehand, meaning nobody knows what’s inside until it’s opened live. Every opening begins with a genuinely sealed pack.',
  },
  {
    num: '02',
    title: 'Every Pull. Proven Live.',
    desc: "Each card is revealed, scanned and verified using its unique QR code, permanently recording every pull as it happens. This creates a live, tamper-proof history that buyers, sellers and viewers can trust with complete confidence.",
  },
  {
    num: '03',
    title: 'Live Pull Rates. Always Accurate.',
    desc: 'Every pull instantly updates the published odds for that mystery batch.\n\nCustomers always see the current pull rates, remaining chase cards and live batch progress—ensuring complete transparency from the first pack to the last.',
  },
  {
    num: '04',
    title: 'Live Market Pricing - Powered by PulseTCG',
    desc: 'Built to order. Priced in real time.Every batch uses live PulseTCG pricing, ensuring accurate valuations and complete transparency from the moment it’s created.\n\nNo outdated valuations. No guesswork. Just real-time pricing.'
  }
];

export const tickerItems = [
  'Near Mint Only',
  'Live Card Pool',
  'Toploaded Hits',
  'Authenticated Singles',
  'Mystery Packs',
  'Full Transparency',
  'Hit Guaranteed',
];

export const CARD_IMAGES = [imgVictini, imgBubbleMew, imgPikachu, imgLugia, imgMew, imgRayquaza];

export { arcanePack, arcaneLogo };

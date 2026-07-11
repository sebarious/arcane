// src/data.ts
import arcanePack from '@/Assets/Arcane_pack.png';
import arcaneLogo from '@/Assets/Link___Arcane.png';
import imgCharizard from '@/Assets/Charizard.png';
import imgUmbreon from '@/Assets/Umbreon.png';
import imgPikachu from '@/Assets/Pikachu.png';
import imgLugia from '@/Assets/Lugia.png';
import imgMew from '@/Assets/Mew.png';
import imgRayquaza from '@/Assets/Rayquaza.png';

export const pulls = [
  { name: 'Charizard ex', set: 'Obsidian Flames', grade: 'PSA 9', rarity: 'Ultra', value: '£85', img: imgCharizard },
  { name: 'Umbreon VMAX', set: 'Evolving Skies', grade: 'PSA 10', rarity: 'Legendary', value: '£350', img: imgUmbreon },
  { name: 'Pikachu VMAX', set: 'Vivid Voltage', grade: 'PSA 9', rarity: 'Rare', value: '£200', img: imgPikachu },
  { name: 'Lugia V', set: 'Silver Tempest', grade: 'PSA 10', rarity: 'Legendary', value: '£180', img: imgLugia },
  { name: 'Mew VMAX', set: 'Fusion Strike', grade: 'PSA 9', rarity: 'Common', value: '£95', img: imgMew },
  { name: 'Rayquaza VMAX', set: 'Evolving Skies', grade: 'PSA 9', rarity: 'God', value: '£420', img: imgRayquaza },
];

export const livePool = [
  { name: 'Charizard V', set: 'Brilliant Stars', grade: 'PSA 9', qty: 1, streamer: '@FlameKing_TV', img: imgCharizard },
  { name: 'Mewtwo ex', set: 'SV 151', grade: 'PSA 9', qty: 2, streamer: '@PsychicPulls', img: imgLugia },
  { name: 'Gardevoir ex', set: 'Scarlet & Violet', grade: 'PSA 9', qty: 1, streamer: '@GardeStream', img: imgUmbreon },
  { name: 'Miraidon ex', set: 'Scarlet & Violet', grade: 'PSA 10', qty: 1, streamer: '@VoltBreaker', img: imgPikachu },
  { name: 'Iron Valiant ex', set: 'Paradox Rift', grade: 'PSA 9', qty: 3, streamer: '@IronPackTV', img: imgRayquaza },
  { name: 'Iono Full Art', set: 'Paldea Evolved', grade: 'PSA 9', qty: 1, streamer: '@IonoStation', img: imgMew },
  { name: 'Pidgeot ex', set: 'Obsidian Flames', grade: 'PSA 9', qty: 2, streamer: '@BirdCatcher99', img: imgCharizard },
  { name: 'Roaring Moon ex', set: 'Paradox Rift', grade: 'PSA 10', qty: 1, streamer: '@MoonPulls', img: imgRayquaza },
];

// src/data.ts
import { Shield, Package, Eye } from 'lucide-vue-next';
import type { Step } from '../../types'; // optional, if you created types.ts
export const steps: Step[] = [
  {
    num: '01',
    icon: Shield,
    title: 'Authenticated Singles',
    desc: 'Every card in our pool is Near-mint condition, verified authentic, toploaded from the moment it arrives.',
  },
  {
    num: '02',
    icon: Package,
    title: 'Sealed Mystery Pack',
    desc: "We pull one card blind from the live pool and seal it into your pack. You won't know which card until you open it — that's the magic.",
  },
  {
    num: '03',
    icon: Eye,
    title: 'Live Pool Transparency',
    desc: 'See exactly which cards are still in your local shop\'s pool before you buy. No blind guessing — full odds visibility, always.',
  },
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

export const CARD_IMAGES = [imgCharizard, imgUmbreon, imgPikachu, imgLugia, imgMew, imgRayquaza];

export { arcanePack, arcaneLogo };

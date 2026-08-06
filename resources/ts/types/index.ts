export enum Rarity {
  Common = 'Common',
  Rare = 'Rare',
  Ultra = 'Ultra',
  Legendary = 'Legendary',
  God = 'God',
}

export type Card = {
  band: string;
  image: string;
  name: string;
  number: string;
  set: string;
}

export type Batch = {
  id: number;
  reference: string;
}

export type Pull = {
  id: number;
  sequence: number;
  sold_at: string;
  batch: Batch;
  card: Card
  store: Store;
};
export type LiveCard = {
  name: string;
  set: string;
  grade: string;
  qty: number;
  streamer: string;
  img: string;
};
export type Step = {
  num: string;
  title: string;
  desc: string;
};

export interface StoreGame {
  value: string;
  label: string;
}

export interface Store {
  id: number;
  slug: string;
  name: string;
  city: string;
  postcode: string;
  games: StoreGame[];
  logo: string;
}

export interface CardListBatch {
  id: number;
  reference: string;
  type: string | null;
  type_label: string | null;
  game: string | null;
  game_label: string | null;
  pack_count: number;
  remaining_packs: number;
  store: {
    slug: string;
    name: string;
    logo: string | null;
  };
  top_card_1_image: string | null;
  top_card_2_image: string | null;
}
export type Pull = {
  name: string;
  set: string;
  grade: string;
  rarity: 'Common' | 'Rare' | 'Ultra' | 'Legendary' | 'God';
  value: string;
  img: string;
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
  icon: any; // lucide-vue-next components are functional components
  title: string;
  desc: string;
};
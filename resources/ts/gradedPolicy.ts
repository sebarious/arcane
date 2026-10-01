/**
 * Customer-facing wording for a rip pack's graded-card policy.
 *
 * The admin labels on App\Enums\RipGradedPolicy are deliberately not reused
 * here: what an operator needs on a form ("Allow graded cards") is not what a
 * buyer needs before paying for something they can't see.
 */
export type GradedPolicy = 'exclude' | 'allow' | 'only';

export interface GradedNotice {
  badge: string;
  /** Longer disclosure for the detail page; null when the pill says it all. */
  detail: string | null;
  /** Text colour; the pill derives its tint and border from it. */
  accent: string;
}

/**
 * Always returns a notice — every pack shows a pill, so "singles only" is
 * stated outright rather than being left for the buyer to infer from the
 * absence of one.
 */
export function gradedNotice(policy: GradedPolicy | undefined): GradedNotice {
  switch (policy) {
    case 'only':
      return {
        badge: 'Graded cards only',
        detail: 'Every card in this pack is a professionally graded slab.',
        accent: '#DCC175',
      };
    case 'allow':
      return {
        badge: 'Includes graded cards',
        detail: 'This pack can pull graded slabs as well as raw singles.',
        accent: '#60a5fa',
      };
    default:
      // Neutral slate, not the game pill's purple — two differently-meaning
      // pills sitting side by side shouldn't read as the same kind of thing.
      return {
        badge: 'Singles only',
        detail: null,
        accent: '#9aa3b2',
      };
  }
}

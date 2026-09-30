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
  detail: string;
  /** Text colour; the badge derives its tint and border from it. */
  accent: string;
}

/**
 * Null for 'exclude' — a raw-card pack is what a buyer already assumes, so
 * saying so adds noise. Only the two cases that change what they're getting
 * are called out.
 */
export function gradedNotice(policy: GradedPolicy | undefined): GradedNotice | null {
  switch (policy) {
    case 'only':
      return {
        badge: 'Graded slabs only',
        detail: 'Every card in this pack is a professionally graded slab.',
        accent: '#DCC175',
      };
    case 'allow':
      return {
        badge: 'May contain slabs',
        detail: 'This pack can pull graded slabs as well as raw cards.',
        accent: '#60a5fa',
      };
    default:
      return null;
  }
}

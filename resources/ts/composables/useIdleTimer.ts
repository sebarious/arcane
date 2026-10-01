import { onMounted, onUnmounted, ref } from 'vue';

/**
 * Fires once after a stretch of no interaction — what both kiosk tablets use
 * to clean themselves up after someone walks away mid-browse.
 *
 * Listens on the capture phase so activity still counts while a modal has
 * focus, and on `visibilitychange` so a tablet that was asleep is treated as
 * idle the moment it wakes rather than waiting out another full window.
 *
 * `enabled` lets the caller suppress it for moments where a reset would be
 * actively harmful — the till must not lock itself mid-payment.
 */
export function useIdleTimer(
  timeoutMs: number,
  onIdle: () => void,
  enabled: () => boolean = () => true,
) {
  const EVENTS = ['pointerdown', 'keydown', 'touchstart', 'wheel', 'scroll'] as const;

  let timer: ReturnType<typeof setTimeout> | null = null;
  const lastActivity = ref(Date.now());

  function fire() {
    if (!enabled()) {
      // Not idle-safe right now; check again rather than cancelling outright,
      // or the timer would never rearm once the blocking state clears.
      schedule();

      return;
    }

    onIdle();
  }

  function schedule() {
    if (timer) clearTimeout(timer);
    timer = setTimeout(fire, timeoutMs);
  }

  function bump() {
    lastActivity.value = Date.now();
    schedule();
  }

  function onVisibility() {
    if (document.visibilityState !== 'visible') return;

    // Timers are throttled or suspended while hidden, so the elapsed time has
    // to be judged from the clock, not from whether the timeout fired.
    if (Date.now() - lastActivity.value >= timeoutMs) {
      fire();

      return;
    }

    schedule();
  }

  onMounted(() => {
    EVENTS.forEach((e) => window.addEventListener(e, bump, { capture: true, passive: true }));
    document.addEventListener('visibilitychange', onVisibility);
    schedule();
  });

  onUnmounted(() => {
    EVENTS.forEach((e) => window.removeEventListener(e, bump, { capture: true }));
    document.removeEventListener('visibilitychange', onVisibility);
    if (timer) clearTimeout(timer);
  });

  return { bump };
}

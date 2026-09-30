import { reactive } from 'vue';

export interface ToastItem {
  id: number;
  kind: 'success' | 'error' | 'status';
  message: string;
}

/**
 * A single reactive list every FlashToast.vue instance renders from — module
 * scope, so it's a genuine app-wide singleton regardless of how many
 * components import it. Two ways things land in here:
 *  1. Imperatively — call toast() directly after an AJAX action succeeds
 *     (wallet top-up, bank details, withdrawal request, ...).
 *  2. Automatically — FlashToast.vue watches the Inertia `flash` shared prop
 *     and calls toast() itself whenever a controller does
 *     `back()->with('success', ...)` (e.g. "Request batch", saving a
 *     shipping address), so most Inertia-form actions need no extra code.
 */
export const toasts = reactive<ToastItem[]>([]);

let nextId = 1;
const AUTO_DISMISS_MS = 5000;

export function toast(message: string, kind: ToastItem['kind'] = 'success'): void {
  const id = nextId++;
  toasts.push({ id, kind, message });

  window.setTimeout(() => dismissToast(id), AUTO_DISMISS_MS);
}

export function dismissToast(id: number): void {
  const index = toasts.findIndex((t) => t.id === id);
  if (index !== -1) toasts.splice(index, 1);
}

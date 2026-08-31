<script setup lang="ts">
interface Props {
  title: string;
  message: string;
  confirmLabel?: string;
  cancelLabel?: string;
  confirming?: boolean;
  tone?: 'default' | 'danger';
}

withDefaults(defineProps<Props>(), { confirming: false, tone: 'default' });

defineEmits<{ confirm: []; cancel: [] }>();
</script>

<template>
  <!-- z-[170] — above both PopupShell (150) and Fireworks (160), since a
  Keep/Sell-back confirmation can be triggered while a legendary/mythic
  celebration is still playing. -->
  <div class="fixed inset-0 z-[170] flex items-center justify-center p-4"
    :style="{ background: 'rgba(6,6,11,0.85)', backdropFilter: 'blur(6px)' }" @click.self="$emit('cancel')">
    <div class="relative w-full max-w-sm bg-[#13101e] border border-[rgba(220,193,117,0.25)] rounded-[14px] p-6">
      <h3 class="font-['Cinzel',sans-serif] font-bold text-lg text-white mb-2">{{ title }}</h3>
      <p class="text-white/60 text-sm font-['Jost',sans-serif] mb-6 leading-relaxed">{{ message }}</p>
      <div class="flex gap-3">
        <button type="button" @click="$emit('cancel')" :disabled="confirming"
          class="flex-1 py-3 border border-white/20 text-white text-xs tracking-[0.15em] uppercase font-bold rounded-[4px] hover:border-white/40 transition-colors disabled:opacity-40">
          {{ cancelLabel ?? 'Cancel' }}
        </button>
        <button type="button" @click="$emit('confirm')" :disabled="confirming"
          :class="tone === 'danger'
            ? 'bg-gradient-to-r from-red-500 to-red-400'
            : 'bg-gradient-to-r from-[#DCC175] to-[#e8d49a]'"
          class="flex-1 py-3 text-black text-xs tracking-[0.15em] uppercase font-bold rounded-[4px] disabled:opacity-40 transition-opacity">
          {{ confirming ? 'Please wait…' : (confirmLabel ?? 'Confirm') }}
        </button>
      </div>
    </div>
  </div>
</template>

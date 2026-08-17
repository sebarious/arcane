<script setup lang="ts">
import { onMounted, ref, watch } from 'vue';
import QRCode from 'qrcode';

const props = withDefaults(defineProps<{
  value: string;
  size?: number;
}>(), {
  size: 150,
});

const canvasEl = ref<HTMLCanvasElement | null>(null);

async function render() {
  if (!canvasEl.value || !props.value) return;
  await QRCode.toCanvas(canvasEl.value, props.value, {
    width: props.size,
    margin: 1,
  });
}

onMounted(render);
watch(() => props.value, render);
</script>

<template>
  <canvas ref="canvasEl" :width="size" :height="size" />
</template>

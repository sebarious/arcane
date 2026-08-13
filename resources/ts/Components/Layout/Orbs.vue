<template>
  <ClientOnly>
    <div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
      <div class="absolute w-[700px] h-[700px] rounded-full orb orb-1" :style="{
        background: 'radial-gradient(circle, rgba(124,58,237,0.14) 0%, transparent 68%)',
        top: '-20%',
        left: '-15%',
      }" />
      <div class="absolute w-[550px] h-[550px] rounded-full orb orb-2" :style="{
        background: 'radial-gradient(circle, rgba(212,160,23,0.1) 0%, transparent 68%)',
        bottom: '5%',
        right: '-10%',
      }" />
      <div class="absolute w-[420px] h-[420px] rounded-full orb orb-3" :style="{
        background: 'radial-gradient(circle, rgba(109,40,217,0.1) 0%, transparent 68%)',
        top: '40%',
        left: '42%',
      }" />
    </div>
  </ClientOnly>
</template>

<style scoped>
/* This layout-level chrome renders on almost every public page and used to
   drift via @vueuse/motion's per-frame JS tween, forever, for as long as the
   page stayed open — three more JS-driven infinite animations stacked on top
   of whatever the page itself is running. Native CSS keyframes hand this off
   to the compositor thread instead, so it no longer competes with scroll/tap
   input handling on the main thread. */
.orb {
  animation-timing-function: ease-in-out;
  animation-iteration-count: infinite;
}

.orb-1 {
  animation-name: orb-drift-1;
  animation-duration: 22s;
}

.orb-2 {
  animation-name: orb-drift-2;
  animation-duration: 26s;
  animation-delay: 5s;
}

.orb-3 {
  animation-name: orb-drift-3;
  animation-duration: 30s;
  animation-delay: 11s;
}

@keyframes orb-drift-1 {
  0%, 100% { transform: translate( 0, 0 ); }
  33.333% { transform: translate( 80px, -60px ); }
  66.667% { transform: translate( -40px, 80px ); }
}

@keyframes orb-drift-2 {
  0%, 100% { transform: translate( 0, 0 ); }
  33.333% { transform: translate( -90px, 70px ); }
  66.667% { transform: translate( 50px, -80px ); }
}

@keyframes orb-drift-3 {
  0%, 100% { transform: translate( 0, 0 ); }
  33.333% { transform: translate( 55px, -40px ); }
  66.667% { transform: translate( -70px, 60px ); }
}
</style>
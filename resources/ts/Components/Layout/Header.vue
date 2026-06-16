<template>
  <!-- Top nav -->
  <header class="sticky top-0 border-b border-arcane-border/60 bg-arcane-bg/90 backdrop-blur z-10">
    <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
      <Link href="/" class="flex items-center gap-2">
      <img src="/images/logo.png" alt="Arcane" class="h-10" />
      </Link>
      <nav class="flex items-center gap-4 text-sm text-white/70">
        <Link href="/stores" class="hover:text-white">Stores</Link>
        <Link href="/sell" class="hover:text-white">Sell to us</Link>
        <template v-if="!user?.id">
          <Link href="/login" class="btn-ghost text-xs">Log in</Link>
          <div class="outline-root">
            <Link href="/apply" class="btn-primary text-xs outline-inner">
            <span>Become a seller</span>
            </Link>
          </div>
        </template>
        <template v-else>
          <div class="outline-root">
            <a href="/dashboard" class="btn-primary text-xs outline-inner">
            <span>Dashboard</span>
            </a>
          </div>
        </template>
      </nav>
    </div>
  </header>
</template>

<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import type { User } from '@/types/global';

const page = usePage();
const user = (page?.props?.auth as any)?.user as User | undefined;

console.log(user);
</script>
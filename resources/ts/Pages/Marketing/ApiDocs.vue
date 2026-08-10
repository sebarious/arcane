<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import Nav from '@/Components/Layout/Nav.vue';
import Footer from '@/Components/Layout/Footer.vue';

defineProps<{ rateLimitPerMinute: number }>();

const endpoints = [
  {
    method: 'GET',
    path: '/api/v1/batches',
    title: 'List your batches',
    description: 'References of every active batch belonging to your store — usually the first call an integration makes, then used to feed the other two endpoints below.',
    curl: `curl "https://arcanepacks.com/api/v1/batches" \\
  -H "Authorization: Bearer $ARCANE_API_TOKEN"`,
    response: `{
  "data": [
    { "reference": "ARC-2026-0001", "status": "committed" },
    { "reference": "ARC-2026-0004", "status": "committed" }
  ]
}`,
    errors: [
      { code: '401', body: '{ "message": "Invalid or missing API token." }' },
    ],
  },
  {
    method: 'GET',
    path: '/api/v1/batches/{reference}',
    title: 'Get an active batch\'s packs',
    description: 'Every pack in one of your batches, along with its card and sale status. Only works for batches that are currently active (committed and not merged away) — a batch reference that\'s still a draft, or was merged into another batch, returns a 409.',
    curl: `curl "https://arcanepacks.com/api/v1/batches/ARC-2026-0001" \\
  -H "Authorization: Bearer $ARCANE_API_TOKEN"`,
    response: `{
  "data": [
    {
      "id": 10350,
      "image_url": "https://pokepulse-static.s3.eu-west-2.amazonaws.com/cards/images/PHANTASMAL-FLAMES/Blowtorch_117_094.webp",
      "title": "Blowtorch",
      "rarity": "Ultra Rare",
      "is_sold": false,
      "sold_at": null,
      "market_price": 2.40,
      "market_price_last_synced_at": "2026-08-02T00:00:00+00:00",
      "pokepulse_last_synced_at": "2026-08-02T18:38:03+00:00",
      "qr_url": "https://arcanepacks.com/q/nkfp8iowm9uf"
    },
    {
      "id": 10351,
      "image_url": "https://pokepulse-static.s3.eu-west-2.amazonaws.com/cards/images/PHANTASMAL-FLAMES/Charizard_004_094.webp",
      "title": "Charizard ex",
      "rarity": "Special Illustration Rare",
      "is_sold": true,
      "sold_at": "2026-08-08T08:38:24+00:00",
      "market_price": 84.00,
      "market_price_last_synced_at": "2026-08-06T00:00:00+00:00",
      "pokepulse_last_synced_at": "2026-08-06T09:12:47+00:00",
      "qr_url": "https://arcanepacks.com/q/7q2vd0mksrat"
    }
  ]
}`,
    errors: [
      { code: '401', body: '{ "message": "Invalid or missing API token." }' },
      { code: '404', body: '{ "message": "Batch not found." }' },
      { code: '409', body: '{ "message": "This batch is not currently active." }' },
    ],
  },
  {
    method: 'POST',
    path: '/api/v1/batches/{reference}/packs/{id}/sold',
    title: 'Mark a pack sold',
    description: 'Marks the given pack\'s card as sold. {id} is the id field from the packs list above. Safe to call more than once on the same pack — re-marking an already-sold pack returns status: "already_sold" instead of erroring, so it\'s safe to wire up to a barcode scanner that might double-scan.',
    curl: `curl -X POST "https://arcanepacks.com/api/v1/batches/ARC-2026-0001/packs/10350/sold" \\
  -H "Authorization: Bearer $ARCANE_API_TOKEN"`,
    response: `{
  "status": "sold",
  "data": {
    "id": 10350,
    "is_sold": true,
    "sold_at": "2026-08-08T08:38:24+00:00"
  }
}`,
    errors: [
      { code: '401', body: '{ "message": "Invalid or missing API token." }' },
      { code: '403', body: '{ "message": "The mark-as-sold endpoint has not been enabled for this store yet." }' },
      { code: '404', body: '{ "message": "Batch not found." }' },
      { code: '404', body: '{ "message": "Pack not found in this batch." }' },
    ],
  },
];
</script>

<template>
  <Head title="API Documentation" />

  <main class="bg-[#0d0b14] overflow-x-hidden min-h-screen">
    <div class="relative shrink-0">
      <div class="flex items-center justify-between px-8 lg:px-[64px] py-[20px] relative w-full">
        <div class="h-[49px] relative shrink-0 w-full">
          <Nav />
        </div>
      </div>
    </div>

    <!-- Hero -->
    <div class="px-8 lg:px-[64px] pt-[60px] pb-[40px] max-w-4xl mx-auto">
      <div class="inline-flex items-center gap-[6px] px-[12px] py-[6px] rounded-[4px] bg-[rgba(124,58,237,0.1)] border border-[rgba(124,58,237,0.3)] mb-[20px]">
        <p class="font-['Jost',sans-serif] font-semibold text-[11px] uppercase text-[#a78bfa]">For sellers · REST · JSON</p>
      </div>
      <p class="font-['Cinzel',sans-serif] font-bold text-[40px] lg:text-[56px] text-white leading-tight">
        API <span class="text-[#c9a84c]">documentation</span>
      </p>
      <p class="font-['Jost',sans-serif] font-normal text-[#a3a3a3] text-[18px] mt-[16px] max-w-2xl leading-relaxed">
        A small REST API for sellers to pull their batches and packs and mark cards
        sold from their own tools — a till, a scanner, a spreadsheet macro, whatever
        you've already got. Three endpoints, plain JSON, token authentication.
      </p>
    </div>

    <!-- Test vs live -->
    <div class="px-8 lg:px-[64px] pb-[40px] max-w-4xl mx-auto">
      <div class="bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] p-6">
        <p class="font-['Cinzel',sans-serif] font-bold text-[19px] text-white mb-[10px]">Test vs live mode</p>
        <p class="font-['Jost',sans-serif] text-sm text-[#a3a3a3] leading-relaxed">
          Every new integration starts in <span class="text-white font-semibold">test mode</span> — every
          endpoint returns a sandbox batch instead of your real inventory, so you can build and test
          against realistic data without touching anything real. Every response carries an
          <span class="font-mono text-white">X-Api-Mode</span> header telling you which mode you're in.
          Once we've reviewed your integration, we switch you to
          <span class="text-white font-semibold">live mode</span> and you start seeing your real batches.
          The <span class="font-mono text-white">sold</span> endpoint has its own separate approval step
          on top of that — see its errors below.
        </p>
      </div>
    </div>

    <!-- Authentication -->
    <div class="px-8 lg:px-[64px] pb-[40px] max-w-4xl mx-auto">
      <div class="bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] p-6">
        <p class="font-['Cinzel',sans-serif] font-bold text-[19px] text-white mb-[10px]">Authentication</p>
        <p class="font-['Jost',sans-serif] text-sm text-[#a3a3a3] leading-relaxed mb-4">
          Every request needs a bearer token in the <span class="font-mono text-white">Authorization</span> header:
        </p>
        <pre class="bg-[#0d0b14] border border-[#3d2f6e] rounded-[8px] p-4 overflow-x-auto text-xs font-mono text-[#a3a3a3]"><code>Authorization: Bearer &lt;your-api-token&gt;</code></pre>
        <p class="font-['Jost',sans-serif] text-sm text-[#a3a3a3] leading-relaxed mt-4">
          Tokens are issued per store, not per user. If you're one of our sellers,
          grab yours from the <span class="text-white">API access</span> page on your
          dashboard — access has to be switched on there before a token will work,
          and API access itself has to be granted to your store first. Not seeing it
          yet? Ask us at
          <a href="mailto:support@arcanepacks.com" class="text-[#c9a84c] underline">support@arcanepacks.com</a>.
        </p>
      </div>
    </div>

    <!-- Rate limits -->
    <div class="px-8 lg:px-[64px] pb-[40px] max-w-4xl mx-auto">
      <div class="bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] p-6">
        <p class="font-['Cinzel',sans-serif] font-bold text-[19px] text-white mb-[10px]">Rate limits</p>
        <p class="font-['Jost',sans-serif] text-sm text-[#a3a3a3] leading-relaxed">
          Two limits apply, independently of each other:
        </p>
        <ul class="mt-3 flex flex-col gap-[8px] font-['Jost',sans-serif] text-sm text-[#a3a3a3] leading-relaxed list-disc list-inside">
          <li><span class="text-white font-semibold">{{ rateLimitPerMinute }} requests per minute</span> — applies across the whole API, same for every store.</li>
          <li><span class="text-white font-semibold">A daily quota</span> — set per store (1,000 requests a day by default); check the API access page on your dashboard for your own limit and how much you've used today.</li>
        </ul>
        <p class="font-['Jost',sans-serif] text-sm text-[#a3a3a3] leading-relaxed mt-3">
          Going over either returns <span class="font-mono text-white">429</span> with a JSON body explaining which one:
        </p>
        <pre class="bg-[#0d0b14] border border-[#3d2f6e] rounded-[8px] p-4 overflow-x-auto text-xs font-mono text-[#a3a3a3] mt-3"><code>{ "message": "Rate limit exceeded. Try again shortly." }
{ "message": "Daily request limit reached. Resets at midnight UTC." }</code></pre>
      </div>
    </div>

    <!-- Endpoints -->
    <div class="px-8 lg:px-[64px] pb-[40px] max-w-4xl mx-auto">
      <p class="font-['Cinzel',sans-serif] font-bold text-[28px] lg:text-[34px] text-white leading-tight mb-[24px]">
        Endpoints
      </p>

      <div class="flex flex-col gap-[24px]">
        <div v-for="endpoint in endpoints" :key="endpoint.path + endpoint.method"
          class="bg-[#13101e] border border-[rgba(220,193,117,0.1)] rounded-[12px] p-6">
          <div class="flex items-center gap-3 flex-wrap">
            <span class="font-mono text-xs font-bold px-2.5 py-1 rounded-[4px]"
              :class="endpoint.method === 'GET'
                ? 'bg-[rgba(34,197,94,0.12)] text-[#22c55e]'
                : 'bg-[rgba(201,168,76,0.12)] text-[#c9a84c]'">
              {{ endpoint.method }}
            </span>
            <span class="font-mono text-sm text-white">{{ endpoint.path }}</span>
          </div>
          <p class="font-['Cinzel',sans-serif] font-bold text-[17px] text-white mt-4 mb-2">{{ endpoint.title }}</p>
          <p class="font-['Jost',sans-serif] text-sm text-[#a3a3a3] leading-relaxed mb-4">{{ endpoint.description }}</p>

          <p class="font-['Jost',sans-serif] font-semibold text-[11px] text-[rgba(255,255,255,0.35)] uppercase tracking-wide mb-2">Request</p>
          <pre class="bg-[#0d0b14] border border-[#3d2f6e] rounded-[8px] p-4 overflow-x-auto text-xs font-mono text-[#a3a3a3]"><code>{{ endpoint.curl }}</code></pre>

          <p class="font-['Jost',sans-serif] font-semibold text-[11px] text-[rgba(255,255,255,0.35)] uppercase tracking-wide mb-2 mt-4">Response — 200 OK</p>
          <pre class="bg-[#0d0b14] border border-[#3d2f6e] rounded-[8px] p-4 overflow-x-auto text-xs font-mono text-[#a3a3a3]"><code>{{ endpoint.response }}</code></pre>

          <p class="font-['Jost',sans-serif] font-semibold text-[11px] text-[rgba(255,255,255,0.35)] uppercase tracking-wide mb-2 mt-4">Errors</p>
          <div class="flex flex-col gap-[6px]">
            <div v-for="(err, i) in endpoint.errors" :key="i" class="flex items-start gap-3 font-mono text-xs">
              <span class="text-red-400 font-bold shrink-0">{{ err.code }}</span>
              <span class="text-[#a3a3a3]">{{ err.body }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- CTA -->
    <div class="px-8 lg:px-[64px] pb-[120px] max-w-4xl mx-auto">
      <div class="bg-[#1a1628] border border-[rgba(124,58,237,0.35)] rounded-[16px] p-[32px] flex flex-col sm:flex-row items-start sm:items-center justify-between gap-[20px]">
        <div>
          <p class="font-['Cinzel',sans-serif] font-bold text-[20px] text-white">
            Already a seller?
          </p>
          <p class="font-['Jost',sans-serif] text-[14px] text-[#a3a3a3] mt-[6px]">
            Manage your API access and grab your token from your dashboard.
          </p>
        </div>
        <div class="flex gap-[12px] shrink-0">
          <Link href="/seller/api-access"
            class="px-6 py-3 rounded-[4px] text-sm font-['Jost',sans-serif] font-bold uppercase tracking-wide text-[#0d0b14]"
            style="background-image: linear-gradient(175.236deg, rgb(201, 168, 76) 0%, rgb(232, 212, 154) 100%);">
            Go to API access
          </Link>
        </div>
      </div>
    </div>
  </main>

  <Footer />
</template>

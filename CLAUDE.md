# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

Arcane (Hokey Poke Games) — a mystery-pack trading card business. The platform lets sellers (retail
partners) buy generated "batches" of sealed packs, each containing a real physical card whose identity is
tracked via a QR code. Customers can also sell cards *to* the business through a public submission flow.
Admin operations run through a Filament panel.

## Commands

### Backend (PHP / Laravel)
- `composer dev` — runs the full local stack concurrently: `php artisan serve`, `queue:listen`, `pail`
  (log tailing), and `npm run dev` (Vite). This is the normal way to run the app locally.
- `php artisan test` or `composer test` — run the PHPUnit suite (clears config cache first).
- `php artisan test --filter=TestName` — run a single test.
- `vendor/bin/pint` — format PHP code (Laravel Pint, no custom `pint.json`, so defaults apply).
- `php artisan migrate` — run migrations (Postgres in dev; sqlite `:memory:` in tests, see `phpunit.xml`).

### Frontend (TypeScript / Vue / Inertia)
- `npm run dev` — Vite dev server (normally run via `composer dev`, not standalone).
- `npm run build` — type-checks (`vue-tsc --noEmit`) then builds client **and** SSR bundles.
- `npm run type-check` — `vue-tsc --noEmit` only.

### Local infra dependencies (see README.md)
- `brew services start redis` — sessions, cache, and queue all use Redis (see `.env.example`).
- `brew services start postgresql@18` — primary database.

## Architecture

### Stack
Laravel 13 + Inertia.js v3 + Vue 3 (TypeScript) SPA, server-rendered via `resources/ts/ssr.ts`. Admin
backend is a **Filament v5** panel (`app/Filament/**`, `app/Providers/Filament/AdminPanelProvider.php`).
Queues/scheduled work run through Horizon (Redis); Reverb provides websockets/broadcasting. Roles are
managed with `spatie/laravel-permission` (`admin` / `seller` roles gate almost everything — see
`bootstrap/app.php` route middleware aliases `role` and `store.live`).

Frontend source lives in `resources/ts` (aliased as `@/`), **not** `resources/js` — Inertia pages are under
`resources/ts/Pages/**`, mirroring `routes/web.php` controller groupings (`Storefront`, `Seller`, `Sell`,
`SellerApplications`, `Marketing`, `Legal`, `Auth`).

### Domain model

**Batch → Pack → CardInventory** is the core chain:
- A `Batch` (`app/Models/Batch.php`) belongs to a `Store` (seller) and has a `type` (`BatchType` enum:
  Sapphire/Ruby/Diamond) and a `game` (`Game` enum: currently only Pokémon is populated). Each type has a
  fixed pack count, sale price, and target margin defined in `config/batches.php`.
- A `Batch` has many `Pack`s (one per physical pack sold), and each `Pack` has one `CardInventory` row (the
  actual card sealed inside). `card_inventory` is the **primary card data table** — it stores PulseAPI card
  fields directly (name, set, rarity, image, etc.) rather than joining out to separate cards/expansions
  tables (those were dropped — see the `2026_08_01_120000_replace_scrydex_with_pulseapi` migration).
- `CardInventory.qr_token` is the physical/digital link: printed on the card's sleeve, scanned via
  `QrScanController` (`/q/{token}`) to confirm a sale (`QrConfirmController`).

**Batch generation** (`App\Services\Batches\BatchGenerator::generate()`) is the most complex piece of
business logic in the codebase:
1. Reads the target pack count/margin/rarity-band distribution for the batch's `game`+`type` from
   `config/banding.php` (rarity band quotas, e.g. Sapphire = 113 common/6 rare/3 super/2 legendary/1
   mythic) and `config/batches.php` (pack count, price, target margin).
2. Refreshes stale prices for the eligible stock pool via `CardPriceSyncer` before bucketing by
   `rarity_band`, since a stale price could have shifted a card into a different band.
3. Randomly samples up to 150 candidate selections, enforcing per-band duplicate limits
   (`config('banding.duplicate_limits')`) and drawing roughly evenly across low/mid/high thirds of each
   band's price range (see `RarityBander`) so the selection isn't skewed to one end — except `mythic`,
   which is left as high-variance chase pool on purpose.
4. Picks the candidate whose margin is closest to the target within a ±10pp window, then — inside a DB
   transaction — creates the `Pack` rows, allocates cards to packs (generating QR tokens), snapshots the
   batch's two priciest cards as `top_card_1_id`/`top_card_2_id` (frozen forever, used for the storefront's
   "Card Lists" thumbnail even after those cards sell), creates the `Invoice`, auto-offsets it against any
   existing store credit, and dispatches `GenerateBatchQrSheetJob` + `SendInvoiceEmailJob`.
- Batches can be merged (`merged_into_batch_id`/`merged_from`) and can carry a `merge_request_batch_id`
  when a seller has requested an existing batch be topped up rather than a new one generated.

**Selling flow** (customer → business): `CustomerSellSubmission` has many `CustomerSellSubmissionItem`s;
offers are computed as a percentage of live market price per rarity tier (`config/selling.php`,
`SellOfferCalculator`), with an optional affiliate bonus when a store's `affiliate_code` is quoted. Accepted
submissions can pay out via `StoreCreditTransaction`, which auto-applies against a store's next invoice
(`StoreCreditService::deductForInvoice`).

**Rapid Intake** (`app/Http/Controllers/Intake/PhoneScanController.php`,
`app/Services/Vision/*`): a phone-camera scanning flow used to intake physical cards. It captures frames,
runs them through Google Cloud Vision (`GoogleVisionClient`) to OCR the card's set number
(`SetCodeExtractor`, `CardNumberExtractor`, `RotatingFrameScanner`), and resolves the result via
`CardRowResolver`. `RAPID_INTAKE_DEBUG_OCR=true` logs raw OCR text + extraction results when a scan format
isn't matching.

**Pricing**: `App\Services\PulseApi\PulseApiClient` is the sole external card-data/price source (PulseAPI —
see [[pulseapi_migration]] context: it replaced Scrydex, and `cards`/`expansions` tables no longer exist).
`CardPriceSyncer` refreshes prices that are older than `PULSEAPI_PRICE_TTL_DAYS`; `PulseApiPriceProvider`
implements the `PriceProvider` interface consumed elsewhere. Prices/costs are stored in USD from the API but
money is otherwise handled in GBP pence throughout the app (`usd_to_gbp()` helper, `CurrencyConverter`).

### Money handling
All monetary amounts are stored as **integer pence** (`*_pence` columns) — never floats. Models expose
pound-float accessors where convenient (e.g. `CardInventory::getCostAttribute()`). VAT is UK margin-scheme
VAT; `config/vat.php`'s `registered` flag controls whether it's actually *shown* on invoices (it's always
computed/stored internally regardless, see `Batch::margin_scheme_vat_pence` /
`Invoice::internal_margin_vat_pence`) — this is a pre-VAT-threshold business, so treat that flag as load-
bearing, not cosmetic.

### Access control
Two roles: `admin` (Filament panel access, gated by `User::canAccessPanel()`) and `seller` (Inertia
dashboard under `/seller/**`, gated by the `role:seller` route middleware). A seller's dashboard is further
gated by the `store.live` middleware (`EnsureSellerStoreIsPublic`) — it redirects to a holding page
(`seller.pending`) until an admin flips `Store::public_page_enabled` AND `status = active`. Sellers can edit
their store's content but not its visibility.

### Reference generators
`Batch`, `Invoice`, and `CustomerSellSubmission` each have a `nextReference()`/`nextNumber()` static that
generates year-scoped, zero-padded sequential identifiers (`ARC-2026-0001`, `INV-2026-0001`,
`SELL-2026-0001`) by querying the max existing value for the current year — follow this pattern if adding
another referenceable entity rather than introducing a new ID scheme.

## Testing

The test suite is currently a placeholder (`tests/Feature/ExampleTest.php`, `tests/Unit/ExampleTest.php`) —
there is no real coverage of the domain logic above yet. Tests run against sqlite in-memory with queues
forced to `sync` and broadcasting/mail disabled (`phpunit.xml`).

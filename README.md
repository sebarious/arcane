# Arcane

Mystery pack platform for Hokey Poke Games. Sellers buy generated batches of sealed packs (Sapphire /
Ruby / Diamond tiers); each pack contains one real, QR-tracked trading card. Customers can also sell cards
in through a public submission flow. Admin operations run through a Filament panel.

Laravel 13 + Inertia.js (Vue 3, TypeScript) + Filament 5. See [CLAUDE.md](CLAUDE.md) for architecture notes.

## Requirements

- PHP 8.3+, Composer
- Node 20+, npm
- PostgreSQL
- Redis (sessions, cache, and queues)

## Setup

```bash
brew services start redis
brew services start postgresql@18

createdb arcane   # or: psql postgres -c "CREATE DATABASE arcane;"

composer install
cp .env.example .env
php artisan key:generate
php artisan migrate

npm install
```

Fill in the required third-party keys in `.env` before running things that depend on them:

- `POKEPULSE_API_KEY` — PulseAPI, the card data/pricing source (required for batch generation and pricing).
- `GOOGLE_VISION_API_KEY` — Rapid Intake's live camera scanner (OCR).
- `STRIPE_KEY` / `STRIPE_SECRET` — payments.
- `RESEND_KEY` — transactional email.
- `REVERB_*` — websockets/broadcasting (defaults work for local dev).

## Running locally

```bash
composer dev
```

This runs the PHP dev server, queue worker, log tailer (`pail`), and the Vite dev server concurrently.

## Testing

```bash
composer test
```

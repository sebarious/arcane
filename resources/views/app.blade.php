<!DOCTYPE html>
<html lang="en" class="dark">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title inertia>{{ config('app.name', 'Arcane') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <meta name="description" content="Authenticated, near‑mint Pokémon singles sealed into mystery packs — one toploaded hit per pack, with a live card pool you can see before you buy.">

    {{-- Default share-link preview (Discord/Facebook/iMessage/etc). Pages that need
    something more specific — a store or batch — override these via Inertia's <Head>. --}}
    <meta property="og:site_name" content="{{ config('app.name', 'Arcane') }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ config('app.name', 'Arcane') }}">
    <meta property="og:description" content="Authenticated, near‑mint Pokémon singles sealed into mystery packs — one toploaded hit per pack, with a live card pool you can see before you buy.">
    <meta property="og:image" content="{{ asset('images/pack.png') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ config('app.name', 'Arcane') }}">
    <meta name="twitter:description" content="Authenticated, near‑mint Pokémon singles sealed into mystery packs — one toploaded hit per pack, with a live card pool you can see before you buy.">
    <meta name="twitter:image" content="{{ asset('images/pack.png') }}">

    @routes
    @vite(['resources/ts/app.ts'])
    @inertiaHead
</head>

<body class="min-h-screen">
    @inertia
</body>

</html>
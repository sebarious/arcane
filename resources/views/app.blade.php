<!DOCTYPE html>
<html lang="en" class="dark">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title inertia>{{ config('app.name', 'Arcane') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <meta name="description" content="Authenticated, near‑mint Pokémon singles sealed into mystery packs — one toploaded hit per pack, with a live card pool you can see before you buy.">

    {{-- Google Fonts, loaded without blocking first paint: preconnect gets the
    cross-origin connection warmed up early, and the media="print" swap trick
    defers the stylesheet's render-blocking effect until after it's fetched
    (the noscript fallback covers browsers with JS disabled). Also preconnect
    to the CDN that serves individual card images (see CardPriceSyncer/
    PulseApiClient) since those load on nearly every storefront page. --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://pokepulse-static.s3.eu-west-2.amazonaws.com">
    @php($fontsHref = 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Cinzel:wght@400;500;600;700;900&family=Jost:wght@300;400;500;600&display=swap')
    <link rel="preload" as="style" href="{{ $fontsHref }}">
    <link rel="stylesheet" href="{{ $fontsHref }}" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="{{ $fontsHref }}"></noscript>

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
<!DOCTYPE html>
<html lang="en" class="dark">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title inertia>{{ config('app.name', 'Arcane') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    @routes
    @vite(['resources/css/app.css', 'resources/ts/app.ts'])
    @inertiaHead
</head>

<body class="min-h-screen">
    @inertia
</body>

</html>
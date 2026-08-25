<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>New seller onboarding submission</title>
</head>

<body style="font-family: Arial, sans-serif; background: #f8fafc; color: #111827; padding: 24px;">
  <div style="max-width: 640px; margin: 0 auto; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 32px;">
    <h1 style="margin-top: 0; font-size: 24px;">New onboarding submission</h1>

    <p>{{ $store->name }} has finished the seller onboarding form and is ready to review.</p>

    <p><strong>Store:</strong> {{ $store->name }}</p>
    <p><strong>Seller:</strong> {{ $store->user->name }} ({{ $store->user->email }})</p>
    <p><strong>Public location:</strong> {{ $store->location ?: '—' }}</p>

    @if($store->description)
    <p><strong>Bio:</strong></p>
    <p style="white-space: pre-line;">{{ $store->description }}</p>
    @endif

    <p style="margin-top: 24px;">
      Review and approve in the admin panel:
      <a href="{{ url("/admin/stores/{$store->id}/edit") }}">{{ url("/admin/stores/{$store->id}/edit") }}</a>
    </p>
  </div>
</body>

</html>

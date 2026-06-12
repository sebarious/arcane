<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>Arcane – Already redeemed</title>
  <style>
    body {
      font-family: system-ui, -apple-system, sans-serif;
      padding: 2rem;
      background: #0a0a0f;
      color: #e5e7eb;
    }

    .card {
      max-width: 480px;
      margin: 0 auto;
      padding: 1.5rem;
      border-radius: 0.75rem;
      background: #111827;
      border: 1px solid #374151;
    }

    h1 {
      font-size: 1.25rem;
      margin-bottom: 0.75rem;
      color: #facc15;
    }

    p {
      margin: 0.25rem 0;
      color: #9ca3af;
    }

    .meta {
      margin-top: 0.75rem;
      font-size: 0.85rem;
      color: #6b7280;
    }
  </style>
</head>

<body>
  <div class="card">
    <h1>Pack already marked as sold</h1>
    <p><strong>Card:</strong> {{ $card->card?->name ?? 'Unknown' }}</p>
    <p><strong>Store:</strong> {{ $store?->name ?? 'Unknown store' }}</p>
    <p><strong>Batch:</strong> {{ $batch?->reference ?? '—' }}</p>
    <p class="meta">
      This code was already redeemed.
      If you believe this is an error, please speak to store staff.
    </p>
  </div>
</body>

</html>
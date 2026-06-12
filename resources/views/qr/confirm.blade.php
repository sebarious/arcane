<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>Arcane – Confirm sale</title>
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
      color: #a78bfa;
    }

    p {
      margin: 0.25rem 0;
      color: #9ca3af;
    }

    form {
      margin-top: 1rem;
    }

    button {
      padding: 0.5rem 1rem;
      border-radius: 0.5rem;
      border: none;
      background: #a78bfa;
      color: #020617;
      font-weight: 600;
      cursor: pointer;
    }

    button:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }
  </style>
</head>

<body>
  <div class="card">
    <h1>Mark pack as sold?</h1>

    <p><strong>Card:</strong> {{ $card->card?->name ?? 'Unknown' }}</p>
    <p><strong>Set:</strong> {{ $card->card?->set_name }} · {{ $card->card?->card_number }}</p>
    <p><strong>Rarity band:</strong> {{ ucfirst($card->rarity_band ?? 'n/a') }}</p>
    <p><strong>Store:</strong> {{ $store?->name ?? 'Unknown store' }}</p>
    <p><strong>Batch:</strong> {{ $batch?->reference ?? '—' }} · Pack #{{ $pack?->sequence_no ?? '—' }}</p>

    <form method="POST" action="{{ route('qr.confirm', ['token' => $card->qr_token]) }}">
      @csrf
      <button type="submit">Confirm sale</button>
    </form>
  </div>
</body>

</html>
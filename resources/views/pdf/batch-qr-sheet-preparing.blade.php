<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>QR sheet preparing – {{ $batch->reference }}</title>
  <meta http-equiv="refresh" content="10">
  <style>
    body {
      font-family: system-ui, sans-serif;
      background: #0a0a0f;
      color: #e8e8f0;
      padding: 2rem;
    }

    .card {
      max-width: 480px;
      margin: 0 auto;
      padding: 1.5rem;
      background: #12121a;
      border: 1px solid #2a2a3a;
      border-radius: 0.75rem;
    }

    h1 {
      color: #a78bfa;
      font-size: 1.25rem;
      margin-bottom: 0.5rem;
    }
  </style>
</head>

<body>
  <div class="card">
    <h1>Preparing QR sheet for {{ $batch->reference }}</h1>
    <p>Generating QR codes for {{ $batch->pack_count }} packs. This usually takes under a minute.</p>
    <p>This page will refresh automatically. You can also reload it manually.</p>
  </div>
</body>

</html>
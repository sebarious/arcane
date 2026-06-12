<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>{{ $batch->reference }} – QR Sheet</title>
  <style>
    @page {
      margin: 15mm;
    }

    body {
      font-family: DejaVu Sans, sans-serif;
      font-size: 10px;
    }

    .header {
      margin-bottom: 8mm;
    }

    .header h1 {
      font-size: 16px;
      margin: 0 0 4px 0;
    }

    .grid {
      display: flex;
      flex-wrap: wrap;
    }

    .cell {
      width: 25%;
      /* 4 per row */
      padding: 4mm 2mm;
      box-sizing: border-box;
    }

    .cell-inner {
      border: 0.2mm solid #444;
      padding: 2mm;
    }

    .qr {
      text-align: center;
      margin-bottom: 2mm;
    }

    .qr svg {
      width: 32mm;
      height: 32mm;
    }

    .meta {
      font-size: 8px;
    }

    .meta strong {
      font-size: 9px;
    }

    .qr img {
      width: 32mm;
      height: 32mm;
    }
  </style>
</head>

<body>
  <div class="header">
    <h1>Arcane – {{ $batch->type->label() }} batch</h1>
    <div>
      Ref: <strong>{{ $batch->reference }}</strong><br>
      Store: <strong>{{ $batch->store?->name }}</strong><br>
      Packs: <strong>{{ $batch->pack_count }}</strong>
    </div>
  </div>

  <div class="grid">
    @foreach ($rows as $row)
    <div class="cell">
      <div class="cell-inner">
        <div class="qr">
          @if (!empty($row['qr_png']))
          <img src="{{ $row['qr_png'] }}" alt="QR code">
          @else
          <span>No QR</span>
          @endif
        </div>
        <div class="meta">
          <strong>#{{ $row['sequence'] }}</strong><br>
          {{ $row['name'] }}<br>
          {{ $row['set'] }} · {{ $row['number'] }}<br>
          Band: {{ ucfirst($row['band'] ?: 'n/a') }}
        </div>
      </div>
    </div>
    @endforeach
  </div>
</body>

</html>
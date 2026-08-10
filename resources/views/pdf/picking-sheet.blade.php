<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>{{ $batch->reference }} – Picking Sheet</title>

  <style>
    @page {
      margin: 32px 40px;
    }

    html,
    body {
      margin: 0;
      padding: 0;
      font-family: DejaVu Sans, sans-serif;
      font-size: 11px;
      color: #111;
    }

    h1 {
      font-size: 16px;
      margin: 0 0 2px;
    }

    .subtitle {
      font-size: 10px;
      color: #555;
      margin: 0 0 18px;
    }

    .lot {
      margin-bottom: 20px;
    }

    .lot-heading {
      font-size: 13px;
      font-weight: bold;
      background: #eee;
      padding: 5px 8px;
      margin: 0 0 4px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    th,
    td {
      text-align: left;
      padding: 4px 8px;
      border-bottom: 1px solid #ddd;
    }

    th {
      font-size: 9px;
      text-transform: uppercase;
      color: #555;
      border-bottom: 1px solid #999;
    }

    .position {
      font-weight: bold;
      font-size: 13px;
      width: 60px;
    }

    .muted {
      color: #555;
    }

    .new-page {
      page-break-before: always;
    }
  </style>
</head>

<body>
  <h1>Picking sheet — {{ $batch->reference }}</h1>
  <p class="subtitle">
    Generated {{ now()->format('d M Y H:i') }}. Each lot is a box, cards stored alphabetically by name.
    Pick top-to-bottom within a lot — the box shrinks as you go, so a position number may repeat once
    the card ahead of it has been pulled.
  </p>

  @foreach ($lots as $lot)
  <div class="lot">
    <p class="lot-heading">Lot: {{ $lot['lot'] }}</p>
    <table>
      <thead>
        <tr>
          <th class="position">Position</th>
          <th>Card</th>
          <th>Set</th>
          <th>Number</th>
          <th>Pack #</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($lot['cards'] as $card)
        <tr>
          <td class="position">{{ $card['position'] }}</td>
          <td>{{ $card['card_name'] }}</td>
          <td class="muted">{{ $card['set_name'] }}</td>
          <td class="muted">{{ $card['card_number'] }}</td>
          <td>{{ $card['pack_sequence'] }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endforeach
</body>

</html>

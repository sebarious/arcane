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

    .variant-badge {
      display: inline-block;
      font-size: 8px;
      font-weight: bold;
      text-transform: uppercase;
      color: #111;
      background: #ffd54a;
      border: 1px solid #b8860b;
      border-radius: 3px;
      padding: 1px 5px;
      margin-left: 6px;
    }

    .new-page {
      page-break-before: always;
    }

    .already-picked-heading {
      font-size: 15px;
      margin: 0 0 2px;
      color: #555;
    }

    .lot.already-picked .lot-heading {
      background: none;
      color: #777;
      border-bottom: 1px solid #ddd;
    }

    .lot.already-picked td {
      color: #777;
    }

    .special-handling-heading {
      font-size: 15px;
      margin: 0 0 2px;
      color: #a33;
    }

    .flag-badge {
      display: inline-block;
      font-size: 9px;
      font-weight: bold;
      text-transform: uppercase;
      border-radius: 3px;
      padding: 2px 7px;
      margin-right: 4px;
    }

    .flag-ebay {
      color: #7a3a00;
      background: #ffe0b3;
      border: 1px solid #cc8400;
    }

    .flag-card-wall {
      color: #0a3d7a;
      background: #cfe3ff;
      border: 1px solid #3d78cc;
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

  @if ($lots->isEmpty())
  <p class="subtitle"><strong>Nothing new to pick</strong> — every card in this batch has already been picked.
    See the reference section below for the full list.</p>
  @endif

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
          <th>Rarity</th>
          <th>Pack #</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($lot['cards'] as $card)
        <tr>
          <td class="position">{{ $card['position'] }}</td>
          <td>
            {{ $card['card_name'] }}
            @foreach ($card['product_badges'] as $badge)
            <span class="variant-badge">{{ $badge }}</span>
            @endforeach
          </td>
          <td class="muted">{{ $card['set_name'] }}</td>
          <td class="muted">{{ $card['card_number'] }}</td>
          <td class="muted">{{ $card['rarity'] }}</td>
          <td>{{ $card['pack_sequence'] }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endforeach

  @if (isset($alreadyPickedLots) && $alreadyPickedLots->isNotEmpty())
  <div class="new-page">
    <h2 class="already-picked-heading">Already picked — reference only, no action needed</h2>
    <p class="subtitle">
      These cards belong to this batch but were already pulled in an earlier picking run (e.g. before a
      later card swap) — they're no longer in their lot's box, so there's no position to give them here.
    </p>

    @foreach ($alreadyPickedLots as $lot)
    <div class="lot already-picked">
      <p class="lot-heading">Lot: {{ $lot['lot'] }}</p>
      <table>
        <thead>
          <tr>
            <th>Card</th>
            <th>Set</th>
            <th>Number</th>
            <th>Rarity</th>
            <th>Pack #</th>
            <th>Picked</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($lot['cards'] as $card)
          <tr>
            <td>
              {{ $card['card_name'] }}
              @foreach ($card['product_badges'] as $badge)
              <span class="variant-badge">{{ $badge }}</span>
              @endforeach
            </td>
            <td>{{ $card['set_name'] }}</td>
            <td>{{ $card['card_number'] }}</td>
            <td>{{ $card['rarity'] }}</td>
            <td>{{ $card['pack_sequence'] }}</td>
            <td>{{ $card['picked_at'] }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @endforeach
  </div>
  @endif

  @if (isset($specialHandling) && $specialHandling->isNotEmpty())
  <div class="new-page">
    <h2 class="special-handling-heading">⚠ Special handling — check before shipping</h2>
    <p class="subtitle">
      These cards are currently marked as listed on eBay or on display in the card wall — they may not
      actually be sitting in their lot's box. Pull them from where they're flagged (and delist/replace on
      eBay if sold) before this batch ships.
    </p>
    <table>
      <thead>
        <tr>
          <th>Card</th>
          <th>Set</th>
          <th>Number</th>
          <th>Pack #</th>
          <th>Flags</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($specialHandling as $card)
        <tr>
          <td>
            {{ $card['card_name'] }}
            @foreach ($card['product_badges'] as $badge)
            <span class="variant-badge">{{ $badge }}</span>
            @endforeach
          </td>
          <td class="muted">{{ $card['set_name'] }}</td>
          <td class="muted">{{ $card['card_number'] }}</td>
          <td>{{ $card['pack_sequence'] }}</td>
          <td>
            @if ($card['on_ebay'])
            <span class="flag-badge flag-ebay">On eBay</span>
            @endif
            @if ($card['in_card_wall'])
            <span class="flag-badge flag-card-wall">Card Wall</span>
            @endif
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif
</body>

</html>

<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>{{ $invoice->number }}</title>
  <style>
    @page {
      margin: 15mm;
    }

    body {
      font-family: DejaVu Sans, sans-serif;
      font-size: 11px;
      color: #111827;
    }

    .header {
      margin-bottom: 10mm;
    }

    .row {
      display: flex;
      justify-content: space-between;
    }

    .box {
      width: 48%;
    }

    h1 {
      font-size: 18px;
      margin: 0 0 4px 0;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 8mm;
    }

    th,
    td {
      padding: 4px 6px;
      border-bottom: 0.2mm solid #e5e7eb;
      text-align: left;
    }

    th {
      background: #f3f4f6;
    }

    .right {
      text-align: right;
    }

    .total-row td {
      border-top: 0.3mm solid #111827;
      font-weight: bold;
    }

    .note {
      margin-top: 8mm;
      font-size: 9px;
      color: #6b7280;
    }
  </style>
</head>

<body>
  <div class="header">
    <div class="row">
      <div class="box">
        <h1>Invoice {{ $invoice->number }}</h1>
        <p>
          <strong>Issue date:</strong> {{ $invoice->issued_on?->format('Y-m-d') ?? '' }}<br>
          <strong>Due date:</strong> {{ $invoice->due_on?->format('Y-m-d') ?? 'On receipt' }}
        </p>
      </div>
      <div class="box" style="text-align:right">
        <p>
          <strong>Arcane</strong><br>
          <!-- Your business details here -->
        </p>
      </div>
    </div>

    <div class="row" style="margin-top:8mm;">
      <div class="box">
        <p>
          <strong>Bill to:</strong><br>
          {{ $invoice->store->name }}<br>
          {{ $invoice->store->address_line_1 }}<br>
          @if($invoice->store->address_line_2)
          {{ $invoice->store->address_line_2 }}<br>
          @endif
          {{ $invoice->store->city }} {{ $invoice->store->postcode }}<br>
          {{ $invoice->store->country }}
        </p>
      </div>
    </div>
  </div>

  @php
  $qty = $invoice->batch?->pack_count ?? 1;
  $unitPence = $qty > 0 ? intdiv($invoice->total_pence, $qty) : $invoice->total_pence;
  @endphp

  <table>
    <thead>
      <tr>
        <th>Description</th>
        <th class="right">Qty</th>
        <th class="right">Unit price</th>
        <th class="right">Line total</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>
          @if($invoice->batch)
          Arcane {{ strtoupper($invoice->batch->type->value) }} batch
          – {{ $invoice->batch->pack_count }} packs
          (Batch ref: {{ $invoice->batch->reference }})
          @else
          Arcane mystery packs
          @endif
        </td>
        <td class="right">
          {{ $qty }}
        </td>
        <td class="right">
          £{{ number_format($unitPence / 100, 2) }}
        </td>
        <td class="right">
          £{{ number_format($invoice->total_pence / 100, 2) }}
        </td>
      </tr>
      <tr class="total-row">
        <td colspan="3" class="right">Total due</td>
        <td class="right">£{{ number_format($invoice->total_pence / 100, 2) }}</td>
      </tr>
    </tbody>
  </table>

  <p class="note">
    This invoice is issued under the VAT second-hand margin scheme for goods.
    VAT is accounted for on the margin and cannot be reclaimed.
  </p>
</body>

</html>
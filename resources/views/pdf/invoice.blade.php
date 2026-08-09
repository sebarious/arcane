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
      color: #1c1a26;
    }

    .brand-bar {
      background: #13101e;
      padding: 8mm 10mm;
      border-radius: 3mm;
      margin-bottom: 8mm;
    }

    .brand-bar table {
      width: 100%;
      border-collapse: collapse;
    }

    .brand-name {
      font-size: 20px;
      font-weight: bold;
      color: #e8d49a;
      letter-spacing: 0.05em;
    }

    .brand-tagline {
      font-size: 9px;
      color: #a3a3a3;
      margin-top: 2px;
    }

    .invoice-title {
      font-size: 16px;
      font-weight: bold;
      color: #ffffff;
    }

    .invoice-number {
      font-size: 10px;
      color: #c9a84c;
      margin-top: 2px;
    }

    .header {
      margin-bottom: 8mm;
    }

    .header table {
      width: 100%;
      border-collapse: collapse;
    }

    .header td {
      vertical-align: top;
    }

    .box {
      width: 50%;
    }

    .label {
      font-size: 9px;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      color: #8a8497;
      margin-bottom: 2px;
    }

    table.items {
      width: 100%;
      border-collapse: collapse;
      margin-top: 4mm;
    }

    table.items th,
    table.items td {
      padding: 6px 8px;
      border-bottom: 0.2mm solid #e5e0ec;
      text-align: left;
    }

    table.items th {
      background: #f5f2fa;
      color: #13101e;
      font-size: 9px;
      text-transform: uppercase;
      letter-spacing: 0.04em;
    }

    .right {
      text-align: right;
    }

    .total-row td {
      border-top: 0.4mm solid #c9a84c;
      border-bottom: none;
      font-weight: bold;
      font-size: 13px;
      color: #13101e;
    }

    .credit-row td {
      color: #1f7a4d;
    }

    .note {
      margin-top: 10mm;
      padding-top: 4mm;
      border-top: 0.2mm solid #e5e0ec;
      font-size: 9px;
      color: #8a8497;
    }

    .footer-brand {
      margin-top: 6mm;
      font-size: 9px;
      color: #c9a84c;
      letter-spacing: 0.08em;
      text-transform: uppercase;
    }
  </style>
</head>

<body>
  <div class="brand-bar">
    <table>
      <tr>
        <td>
          <div class="brand-name">ARCANE</div>
          <div class="brand-tagline">Authenticated Pokemon mystery packs</div>
        </td>
        <td class="right">
          <div class="invoice-title">INVOICE</div>
          <div class="invoice-number">{{ $invoice->number }}</div>
        </td>
      </tr>
    </table>
  </div>

  <div class="header">
    <table>
      <tr>
        <td class="box">
          <div class="label">Bill to</div>
          <p>
            <strong>{{ $invoice->store->name }}</strong><br>
            {{ $invoice->store->address_line_1 }}<br>
            @if($invoice->store->address_line_2)
            {{ $invoice->store->address_line_2 }}<br>
            @endif
            {{ $invoice->store->city }} {{ $invoice->store->postcode }}<br>
            {{ $invoice->store->country }}
          </p>
        </td>
        <td class="box right">
          <div class="label">Issue date</div>
          <p>{{ $invoice->issued_on?->format('d M Y') ?? '' }}</p>
          <div class="label">Due date</div>
          <p>{{ $invoice->due_on?->format('d M Y') ?? 'On receipt' }}</p>
        </td>
      </tr>
    </table>
  </div>

  @php
  $qty = $invoice->batch?->pack_count ?? 1;
  $unitPence = $qty > 0 ? intdiv($invoice->total_pence, $qty) : $invoice->total_pence;
  $vatRegistered = config('vat.registered');
  $exVatPence = $invoice->total_pence - $invoice->internal_margin_vat_pence;
  @endphp

  <table class="items">
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
      @if($vatRegistered)
      <tr>
        <td colspan="3" class="right">Subtotal (ex VAT)</td>
        <td class="right">£{{ number_format($exVatPence / 100, 2) }}</td>
      </tr>
      <tr>
        <td colspan="3" class="right">VAT</td>
        <td class="right">No VAT (Margin Scheme)</td>
      </tr>
      <tr>
        <td colspan="3" class="right">Total (incl. VAT)</td>
        <td class="right">£{{ number_format($invoice->total_pence / 100, 2) }}</td>
      </tr>
      @else
      <tr>
        <td colspan="3" class="right">Subtotal</td>
        <td class="right">£{{ number_format($invoice->total_pence / 100, 2) }}</td>
      </tr>
      @endif
      @if($invoice->credit_applied_pence > 0)
      <tr class="credit-row">
        <td colspan="3" class="right">Store credit applied</td>
        <td class="right">-£{{ number_format($invoice->credit_applied_pence / 100, 2) }}</td>
      </tr>
      @endif
      <tr class="total-row">
        <td colspan="3" class="right">Total due</td>
        <td class="right">£{{ number_format($invoice->amount_due_pence / 100, 2) }}</td>
      </tr>
    </tbody>
  </table>

  @if($vatRegistered)
  <p class="note">
    This invoice is issued under the VAT second-hand margin scheme for goods.
    VAT is accounted for on the margin and cannot be reclaimed.
  </p>
  @endif
</body>

</html>

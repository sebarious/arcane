<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>{{ $creditNote->number }}</title>
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

    .watermark {
      position: fixed;
      top: 90mm;
      left: 15mm;
      width: 180mm;
      text-align: center;
      transform: rotate(-30deg);
      font-size: 100px;
      font-weight: bold;
      letter-spacing: 0.1em;
      color: #1f7a4d;
      opacity: 0.16;
      z-index: -1;
    }
  </style>
</head>

<body>
  {{-- Only ever reflects the credit note's state at download time — same
  "downloaded again later" caveat as the invoice PDF's PAID watermark. --}}
  @if($creditNote->status === 'applied')
  <div class="watermark">APPLIED</div>
  @elseif($creditNote->status === 'paid')
  <div class="watermark">PAID</div>
  @endif
  <div class="brand-bar">
    <table>
      <tr>
        <td>
          <div class="brand-name">ARCANE</div>
          <div class="brand-tagline">Authenticated Pokemon mystery packs</div>
        </td>
        <td class="right">
          <div class="invoice-title">CREDIT NOTE</div>
          <div class="invoice-number">{{ $creditNote->number }}</div>
        </td>
      </tr>
    </table>
  </div>

  <div class="header">
    <table>
      <tr>
        <td class="box">
          <div class="label">Issued to</div>
          <p>
            <strong>{{ $creditNote->store->name }}</strong><br>
            {{ $creditNote->store->address_line_1 }}<br>
            @if($creditNote->store->address_line_2)
            {{ $creditNote->store->address_line_2 }}<br>
            @endif
            {{ $creditNote->store->city }} {{ $creditNote->store->postcode }}<br>
            {{ $creditNote->store->country }}
          </p>
        </td>
        <td class="box right">
          <div class="label">Issue date</div>
          <p>{{ $creditNote->created_at?->format('d M Y') ?? '' }}</p>
          @if($creditNote->invoice)
          <div class="label">Applied to invoice</div>
          <p>{{ $creditNote->invoice->number }}</p>
          @endif
        </td>
      </tr>
    </table>
  </div>

  <table class="items">
    <thead>
      <tr>
        <th>Description</th>
        <th class="right">Amount</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>{{ $creditNote->reason }}</td>
        <td class="right">£{{ number_format($creditNote->amount_pence / 100, 2) }}</td>
      </tr>
      <tr class="total-row">
        <td class="right">Total credit</td>
        <td class="right">£{{ number_format($creditNote->amount_pence / 100, 2) }}</td>
      </tr>
    </tbody>
  </table>

  <p class="note">
    This credit note will be applied against a future invoice or paid back to you directly —
    whichever is confirmed on your account.
  </p>
</body>

</html>

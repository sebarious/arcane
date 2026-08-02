<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>New customer sell submission</title>
</head>

<body style="font-family: Arial, sans-serif; background: #f8fafc; color: #111827; padding: 24px;">
  <div style="max-width: 640px; margin: 0 auto; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 32px;">
    <h1 style="margin-top: 0; font-size: 24px;">New customer sell submission</h1>

    <p>A new customer sell submission has been received.</p>

    <p><strong>Reference:</strong> {{ $submission->reference }}</p>
    <p><strong>Customer:</strong> {{ $submission->customer_name }}</p>
    <p><strong>Email:</strong> {{ $submission->customer_email }}</p>
    <p><strong>Phone:</strong> {{ $submission->customer_phone ?: '—' }}</p>

    @if($submission->items->isNotEmpty())
    <table style="width: 100%; border-collapse: collapse; margin: 16px 0;">
      <thead>
        <tr>
          <th style="text-align: left; padding: 6px 0; border-bottom: 1px solid #e5e7eb;">Card</th>
          <th style="text-align: right; padding: 6px 0; border-bottom: 1px solid #e5e7eb;">Qty</th>
          <th style="text-align: right; padding: 6px 0; border-bottom: 1px solid #e5e7eb;">Market</th>
          <th style="text-align: right; padding: 6px 0; border-bottom: 1px solid #e5e7eb;">Offer</th>
        </tr>
      </thead>
      <tbody>
        @foreach($submission->items as $item)
        <tr>
          <td style="padding: 6px 0; border-bottom: 1px solid #f3f4f6;">
            {{ $item->card_name }} @if($item->set_name) ({{ $item->set_name }} {{ $item->card_number }}) @endif
            <span style="color: #6b7280;">— {{ $item->band }}</span>
          </td>
          <td style="text-align: right; padding: 6px 0; border-bottom: 1px solid #f3f4f6;">{{ $item->quantity }}</td>
          <td style="text-align: right; padding: 6px 0; border-bottom: 1px solid #f3f4f6;">£{{ number_format($item->market_value_pence / 100, 2) }}</td>
          <td style="text-align: right; padding: 6px 0; border-bottom: 1px solid #f3f4f6;">£{{ number_format($item->total_offer_pence / 100, 2) }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>

    <p><strong>Indicative total offer: £{{ number_format($submission->estimated_value_pence / 100, 2) }}</strong></p>
    @endif

    @if($submission->description)
    <p><strong>Notes:</strong></p>
    <p style="white-space: pre-line;">{{ $submission->description }}</p>
    @endif

    <p style="margin-top: 24px;">
      Review in admin:
      <a href="{{ url('/admin/customer-sell-submissions') }}">
        {{ url('/admin/customer-sell-submissions') }}
      </a>
    </p>
  </div>
</body>

</html>
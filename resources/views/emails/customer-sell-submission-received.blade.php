<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>We received your sell submission</title>
</head>

<body style="font-family: Arial, sans-serif; background: #f8fafc; color: #111827; padding: 24px;">
  <div style="max-width: 640px; margin: 0 auto; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 32px;">
    <h1 style="margin-top: 0; font-size: 24px;">Thanks for your submission</h1>

    <p>Hi {{ $submission->customer_name }},</p>

    <p>
      We’ve received your sell submission and our team will review it shortly.
    </p>

    <p><strong>Reference:</strong> {{ $submission->reference }}</p>

    @if($submission->items->isNotEmpty())
    <table style="width: 100%; border-collapse: collapse; margin: 16px 0;">
      <thead>
        <tr>
          <th style="text-align: left; padding: 6px 0; border-bottom: 1px solid #e5e7eb;">Card</th>
          <th style="text-align: right; padding: 6px 0; border-bottom: 1px solid #e5e7eb;">Qty</th>
          <th style="text-align: right; padding: 6px 0; border-bottom: 1px solid #e5e7eb;">Offer</th>
        </tr>
      </thead>
      <tbody>
        @foreach($submission->items as $item)
        <tr>
          <td style="padding: 6px 0; border-bottom: 1px solid #f3f4f6;">
            {{ $item->card_name }} @if($item->set_name) ({{ $item->set_name }} {{ $item->card_number }}) @endif
          </td>
          <td style="text-align: right; padding: 6px 0; border-bottom: 1px solid #f3f4f6;">{{ $item->quantity }}</td>
          <td style="text-align: right; padding: 6px 0; border-bottom: 1px solid #f3f4f6;">£{{ number_format($item->total_offer_pence / 100, 2) }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>

    <p><strong>Indicative total offer: £{{ number_format($submission->estimated_value_pence / 100, 2) }}</strong></p>

    <p>
      We’ll confirm the final price once your cards arrive and have been checked by our team.
    </p>
    @else
    <p>
      We’ll review the details you provided and then get back to you with an update or offer.
    </p>
    @endif

    <p>
      Best regards,<br>
      The Arcane Team
    </p>
  </div>
</body>

</html>
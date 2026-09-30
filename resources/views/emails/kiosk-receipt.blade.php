<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>Receipt {{ $order->reference }}</title>
</head>

<body style="font-family: Arial, sans-serif; background: #f8fafc; color: #111827; padding: 24px;">
  <div style="max-width: 640px; margin: 0 auto; background: #13101e; border-radius: 12px 12px 0 0; padding: 24px 32px;">
    <span style="font-size: 20px; font-weight: bold; color: #e8d49a; letter-spacing: 0.05em;">ARCANE</span>
  </div>
  <div style="max-width: 640px; margin: 0 auto; background: #ffffff; border: 1px solid #e5e7eb; border-top: none; border-radius: 0 0 12px 12px; padding: 32px;">
    <h1 style="margin-top: 0; font-size: 22px;">Thanks for your purchase</h1>

    <p style="color: #6b7280; margin-bottom: 24px;">
      Receipt <strong style="color: #111827;">{{ $order->reference }}</strong>
      &middot; {{ optional($order->paid_at)->format('j F Y, H:i') ?? $order->created_at->format('j F Y, H:i') }}
    </p>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 8px;">
      <thead>
        <tr>
          <th style="text-align: left; padding: 8px 0; border-bottom: 1px solid #e5e7eb; font-size: 13px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">Item</th>
          <th style="text-align: right; padding: 8px 0; border-bottom: 1px solid #e5e7eb; font-size: 13px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">Price</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($items as $item)
          <tr>
            <td style="padding: 10px 0; border-bottom: 1px solid #f3f4f6;">
              {{ $item->card_name }}
              @if ($item->set_name)
                <span style="display: block; color: #6b7280; font-size: 13px;">
                  {{ $item->set_name }}{{ $item->card_number ? ' · '.$item->card_number : '' }}
                </span>
              @endif
            </td>
            <td style="padding: 10px 0; border-bottom: 1px solid #f3f4f6; text-align: right; white-space: nowrap;">
              £{{ number_format($item->unit_price_pence / 100, 2) }}
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 24px;">
      @if ($order->discount_pence > 0)
        <tr>
          <td style="padding: 8px 0; color: #6b7280;">Subtotal</td>
          <td style="padding: 8px 0; text-align: right; color: #6b7280;">
            £{{ number_format($order->subtotal_pence / 100, 2) }}
          </td>
        </tr>
        <tr>
          <td style="padding: 8px 0; color: #6b7280;">
            {{ $order->discount_type === 'percent' ? "Discount ({$order->discount_value}%)" : 'Discount' }}
          </td>
          <td style="padding: 8px 0; text-align: right; color: #6b7280;">
            &minus;£{{ number_format($order->discount_pence / 100, 2) }}
          </td>
        </tr>
      @endif
      <tr>
        <td style="padding: 12px 0 0; border-top: 2px solid #e5e7eb; font-weight: bold; font-size: 17px;">Total paid</td>
        <td style="padding: 12px 0 0; border-top: 2px solid #e5e7eb; text-align: right; font-weight: bold; font-size: 20px; color: #c9a84c;">
          £{{ number_format($order->total_pence / 100, 2) }}
        </td>
      </tr>
    </table>

    <p style="color: #6b7280; font-size: 14px;">
      Paid by card at the Arcane trade counter, inside Hokey Poke Games.
      Keep this email as proof of purchase.
    </p>

    <p style="color: #6b7280; font-size: 14px;">
      Browse what else we have in stock at
      <a href="{{ rtrim(config('app.url'), '/') }}/catalogue" style="color: #7c3aed;">arcanepacks.com/catalogue</a>.
    </p>
  </div>
</body>

</html>

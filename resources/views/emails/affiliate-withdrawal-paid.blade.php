<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>Your Arcane withdrawal has been paid</title>
</head>

<body style="font-family: Arial, sans-serif; background: #f8fafc; color: #111827; padding: 24px;">
  <div style="max-width: 640px; margin: 0 auto; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 32px;">
    <h1 style="margin-top: 0; font-size: 24px;">Withdrawal paid</h1>

    <p>Hi {{ $withdrawal->affiliate->user->name }},</p>

    <p>
      Your withdrawal of <strong>£{{ number_format($withdrawal->amount_pence / 100, 2) }}</strong> has been paid
      to your account ending {{ substr($withdrawal->bank_account_number, -4) }}.
    </p>

    <p>
      <a href="{{ url('/affiliate/withdrawals') }}" style="display:inline-block; padding:12px 18px; background:#111827; color:#ffffff; text-decoration:none; border-radius:8px;">
        View your withdrawals
      </a>
    </p>

    <p>
      Best regards,<br>
      The Arcane Team
    </p>
  </div>
</body>

</html>

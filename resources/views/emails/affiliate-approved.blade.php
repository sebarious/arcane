<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>Your Arcane affiliate account is approved</title>
</head>

<body style="font-family: Arial, sans-serif; background: #f8fafc; color: #111827; padding: 24px;">
  <div style="max-width: 640px; margin: 0 auto; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 32px;">
    <h1 style="margin-top: 0; font-size: 24px;">You're approved!</h1>

    <p>Hi {{ $affiliate->user->name }},</p>

    <p>
      Good news — your Arcane affiliate account has been approved. Your dashboard is fully unlocked and your
      affiliate code is now live.
    </p>

    <p><strong>Your affiliate code:</strong> {{ $affiliate->affiliate_code }}</p>

    <p>
      Quote it on our Sell to Us page to earn credit, and add your bank details from your dashboard whenever
      you're ready to request a withdrawal.
    </p>

    <p>
      <a href="{{ url('/affiliate') }}" style="display:inline-block; padding:12px 18px; background:#111827; color:#ffffff; text-decoration:none; border-radius:8px;">
        Go to your dashboard
      </a>
    </p>

    <p>
      Best regards,<br>
      The Arcane Team
    </p>
  </div>
</body>

</html>

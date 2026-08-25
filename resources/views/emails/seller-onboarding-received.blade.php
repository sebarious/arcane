<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>We've received your Arcane onboarding details</title>
</head>

<body style="font-family: Arial, sans-serif; background: #f8fafc; color: #111827; padding: 24px;">
  <div style="max-width: 640px; margin: 0 auto; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 32px;">
    <h1 style="margin-top: 0; font-size: 24px;">Thanks — we've got it</h1>

    <p>Hi {{ $store->user->name }},</p>

    <p>
      We've received your onboarding details for <strong>{{ $store->name }}</strong>, including your logo if you
      uploaded one. Our team is reviewing everything now.
    </p>

    <p>
      We'll email you as soon as your storefront is live and your dashboard is fully unlocked.
    </p>

    <p>
      Questions in the meantime? Reach us at
      <a href="mailto:{{ config('mail.from.address') }}">{{ config('mail.from.address') }}</a>.
    </p>

    <p>
      Best regards,<br>
      The Arcane Team
    </p>
  </div>
</body>

</html>

<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>Your Arcane storefront is live</title>
</head>

<body style="font-family: Arial, sans-serif; background: #f8fafc; color: #111827; padding: 24px;">
  <div style="max-width: 640px; margin: 0 auto; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 32px;">
    <h1 style="margin-top: 0; font-size: 24px;">You're live!</h1>

    <p>Hi {{ $store->user->name }},</p>

    <p>
      Great news — we've reviewed your onboarding details and <strong>{{ $store->name }}</strong> is now live on
      Arcane. Your dashboard is fully unlocked and your store now appears on our storefront list.
    </p>

    <p>
      <a href="{{ route('stores.show', $store) }}" style="display:inline-block; padding:12px 18px; background:#111827; color:#ffffff; text-decoration:none; border-radius:8px;">
        View your storefront
      </a>
    </p>

    <p>
      You can update your bio, logo, location, and social links any time from your dashboard's profile page.
    </p>

    <p>
      Best regards,<br>
      The Arcane Team
    </p>
  </div>
</body>

</html>

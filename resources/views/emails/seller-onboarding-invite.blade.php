<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>Complete your Arcane storefront onboarding</title>
</head>

<body style="font-family: Arial, sans-serif; background: #f8fafc; color: #111827; padding: 24px;">
  <div style="max-width: 640px; margin: 0 auto; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 32px;">
    <h1 style="margin-top: 0; font-size: 24px;">Let's finish setting up your storefront</h1>

    <p>Hi {{ $store->user->name }},</p>

    <p>
      We just need a few more details to get <strong>{{ $store->name }}</strong> live on Arcane — your bio,
      public location, and a logo.
    </p>

    <p>
      Click below to log in and complete your onboarding:
    </p>

    <p>
      <a href="{{ $resetUrl }}" style="display:inline-block; padding:12px 18px; background:#111827; color:#ffffff; text-decoration:none; border-radius:8px;">
        Complete onboarding
      </a>
    </p>

    <p>
      If the button doesn’t work, copy and paste this URL into your browser:
    </p>

    <p style="word-break: break-all;">
      <a href="{{ $resetUrl }}">{{ $resetUrl }}</a>
    </p>

    <p>
      Once you've submitted it, we'll review it and let you know as soon as you're live.
    </p>

    <p>
      Best regards,<br>
      The Arcane Team
    </p>
  </div>
</body>

</html>

<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>Your Arcane seller account has been approved</title>
</head>

<body style="font-family: Arial, sans-serif; background: #f8fafc; color: #111827; padding: 24px;">
  <div style="max-width: 640px; margin: 0 auto; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 32px;">
    <h1 style="margin-top: 0; font-size: 24px;">Your Arcane account is ready</h1>

    <p>Hi {{ $user->name }},</p>

    <p>
      Great news — your application to become an Arcane seller has been approved.
    </p>

    <p><strong>Store:</strong> {{ $store->name }}</p>
    <p><strong>Login email:</strong> {{ $user->email }}</p>

    <p>
      To activate your access, please set your password using the link below:
    </p>

    <p>
      <a href="{{ $resetUrl }}" style="display:inline-block; padding:12px 18px; background:#111827; color:#ffffff; text-decoration:none; border-radius:8px;">
        Set your password
      </a>
    </p>

    <p>
      If the button doesn’t work, copy and paste this URL into your browser:
    </p>

    <p style="word-break: break-all;">
      <a href="{{ $resetUrl }}">{{ $resetUrl }}</a>
    </p>

    <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 28px 0;">

    <h2 style="font-size: 18px; margin-bottom: 8px;">One more thing — complete your storefront onboarding</h2>

    <p>
      Attached to this email is your <strong>Arcane Seller Onboarding</strong> form. Please fill it out
      (you can type directly into the PDF using Adobe Acrobat Reader or Preview) and email it back to us at
      <a href="mailto:{{ config('mail.from.address') }}">{{ config('mail.from.address') }}</a> along with your logo.
    </p>

    <p>Your logo should be:</p>
    <ul>
      <li>600 &times; 600 pixels</li>
      <li>PNG format</li>
    </ul>

    <p>
      Once we've received both, we'll set up your public storefront and let you know as soon as it's live.
    </p>

    <p>
      Best regards,<br>
      The Arcane Team
    </p>
  </div>
</body>

</html>
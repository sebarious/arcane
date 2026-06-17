<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>We received your application</title>
</head>

<body style="font-family: Arial, sans-serif; background: #f8fafc; color: #111827; padding: 24px;">
  <div style="max-width: 640px; margin: 0 auto; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 32px;">
    <h1 style="margin-top: 0; font-size: 24px;">Thanks for applying to Arcane</h1>

    <p>Hi {{ $application->contact_name }},</p>

    <p>
      We’ve received your application to become an Arcane seller for
      <strong>{{ $application->business_name }}</strong>.
    </p>

    <p>
      Our team will review your application and get back to you as soon as possible.
    </p>

    <p>
      Best regards,<br>
      The Arcane Team
    </p>
  </div>
</body>

</html>
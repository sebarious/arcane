<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>Your Arcane seller application</title>
</head>

<body style="font-family: Arial, sans-serif; background: #f8fafc; color: #111827; padding: 24px;">
  <div style="max-width: 640px; margin: 0 auto; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 32px;">
    <h1 style="margin-top: 0; font-size: 24px;">Thank you for applying</h1>

    <p>Hi {{ $application->contact_name }},</p>

    <p>
      Thank you for taking the time to apply to become an Arcane seller.
      We’ve reviewed your application and, unfortunately, we won’t be moving forward at this time.
    </p>

    @if(! empty($application->decline_reason))
    <p><strong>Notes from our team:</strong></p>
    <p style="white-space: pre-line;">{{ $application->decline_reason }}</p>
    @endif

    <p>
      We really appreciate your interest in Arcane, and we may be in touch again in future as the platform grows.
    </p>

    <p>
      Best regards,<br>
      The Arcane Team
    </p>
  </div>
</body>

</html>
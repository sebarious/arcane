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

    <p>
      We’ll review the images and details you provided and then get back to you with an update or offer.
    </p>

    <p>
      Best regards,<br>
      The Arcane Team
    </p>
  </div>
</body>

</html>
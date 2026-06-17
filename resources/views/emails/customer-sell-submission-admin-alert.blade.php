<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>New customer sell submission</title>
</head>

<body style="font-family: Arial, sans-serif; background: #f8fafc; color: #111827; padding: 24px;">
  <div style="max-width: 640px; margin: 0 auto; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 32px;">
    <h1 style="margin-top: 0; font-size: 24px;">New customer sell submission</h1>

    <p>A new customer sell submission has been received.</p>

    <p><strong>Reference:</strong> {{ $submission->reference }}</p>
    <p><strong>Customer:</strong> {{ $submission->customer_name }}</p>
    <p><strong>Email:</strong> {{ $submission->customer_email }}</p>
    <p><strong>Phone:</strong> {{ $submission->customer_phone ?: '—' }}</p>

    @if($submission->description)
    <p><strong>Description:</strong></p>
    <p style="white-space: pre-line;">{{ $submission->description }}</p>
    @endif

    <p style="margin-top: 24px;">
      Review in admin:
      <a href="{{ url('/admin/customer-sell-submissions') }}">
        {{ url('/admin/customer-sell-submissions') }}
      </a>
    </p>
  </div>
</body>

</html>
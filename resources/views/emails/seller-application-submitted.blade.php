<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>New seller application</title>
</head>

<body style="font-family: Arial, sans-serif; background: #f8fafc; color: #111827; padding: 24px;">
  <div style="max-width: 640px; margin: 0 auto; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 32px;">
    <h1 style="margin-top: 0; font-size: 24px;">New seller application</h1>

    <p>A new Arcane seller application has been submitted.</p>

    <p><strong>Business name:</strong> {{ $application->business_name }}</p>
    <p><strong>Contact name:</strong> {{ $application->contact_name }}</p>
    <p><strong>Email:</strong> {{ $application->contact_email }}</p>
    <p><strong>Phone:</strong> {{ $application->phone ?: '—' }}</p>
    <p><strong>Location:</strong> {{ $application->city }}, {{ $application->postcode }}, {{ $application->country }}</p>

    @if($application->about)
    <p><strong>About:</strong></p>
    <p style="white-space: pre-line;">{{ $application->about }}</p>
    @endif

    <p style="margin-top: 24px;">
      Review this in the admin panel:
      <a href="{{ url('/admin/seller-applications') }}">{{ url('/admin/seller-applications') }}</a>
    </p>
  </div>
</body>

</html>
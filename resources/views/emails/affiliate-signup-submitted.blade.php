<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>New affiliate signup</title>
</head>

<body style="font-family: Arial, sans-serif; background: #f8fafc; color: #111827; padding: 24px;">
  <div style="max-width: 640px; margin: 0 auto; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 32px;">
    <h1 style="margin-top: 0; font-size: 24px;">New affiliate signup</h1>

    <p>Someone has signed up to become an Arcane affiliate and is waiting for approval.</p>

    <p><strong>Name:</strong> {{ $affiliate->user->name }}</p>
    <p><strong>Email:</strong> {{ $affiliate->user->email }}</p>
    <p><strong>Affiliate code:</strong> {{ $affiliate->affiliate_code }}</p>

    <p style="margin-top: 24px;">
      Review and approve in the admin panel:
      <a href="{{ url("/admin/affiliates/{$affiliate->id}/edit") }}">{{ url("/admin/affiliates/{$affiliate->id}/edit") }}</a>
    </p>
  </div>
</body>

</html>

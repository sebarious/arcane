<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>Digital Rip needs posting</title>
</head>

<body style="font-family: Arial, sans-serif; background: #f8fafc; color: #111827; padding: 24px;">
  <div style="max-width: 640px; margin: 0 auto; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 32px;">
    <h1 style="margin-top: 0; font-size: 24px;">A Digital Rip needs posting</h1>

    <p>A customer has chosen to keep their pulled card — it needs pulling from stock and posting out.</p>

    <p><strong>Customer:</strong> {{ $rip->order->user->name }}</p>
    <p><strong>Email:</strong> {{ $rip->order->user->email }}</p>
    <p><strong>Pack:</strong> {{ $rip->pack_name }}</p>
    <p><strong>Card:</strong> {{ $rip->card->card_name }} @if($rip->card->set_name) ({{ $rip->card->set_name }} {{ $rip->card->card_number }}) @endif</p>
    <p><strong>Decided:</strong> {{ $rip->decided_at->format('d M Y, H:i') }}</p>

    <p style="margin-top: 24px;">
      Process it in admin:
      <a href="{{ url('/admin/rips') }}">
        {{ url('/admin/rips') }}
      </a>
    </p>
  </div>
</body>

</html>

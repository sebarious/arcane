<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>Batch {{ $batch->reference }} has shipped</title>
</head>

<body style="font-family: Arial, sans-serif; background: #f8fafc; color: #111827; padding: 24px;">
  <div style="max-width: 640px; margin: 0 auto; background: #13101e; border-radius: 12px 12px 0 0; padding: 24px 32px;">
    <span style="font-size: 20px; font-weight: bold; color: #e8d49a; letter-spacing: 0.05em;">ARCANE</span>
  </div>
  <div style="max-width: 640px; margin: 0 auto; background: #ffffff; border: 1px solid #e5e7eb; border-top: none; border-radius: 0 0 12px 12px; padding: 32px;">
    <h1 style="margin-top: 0; font-size: 22px;">Your batch is on its way</h1>

    <p>Hi {{ $batch->store->name }},</p>

    <p>
      Batch {{ $batch->reference }} ({{ $batch->pack_count }} packs) has shipped.
    </p>

    @if($batch->tracking_number)
    <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
      <tr>
        <td style="padding: 8px 0; color: #6b7280;">Tracking number</td>
        <td style="padding: 8px 0; text-align: right; font-weight: bold;">
          {{ $batch->tracking_number }}
        </td>
      </tr>
    </table>
    @endif

    <p style="text-align: center; margin: 28px 0;">
      <a href="{{ $batch->tracking_url }}"
        style="display: inline-block; background: #c9a84c; color: #0d0b14; font-weight: bold; text-decoration: none; padding: 12px 28px; border-radius: 6px;">
        Track your shipment
      </a>
    </p>

    <p>
      If you have any questions about this delivery, just reply to this email.
    </p>

    <p>
      Best regards,<br>
      The Arcane Team
    </p>
  </div>
</body>

</html>

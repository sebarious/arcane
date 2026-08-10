<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>Warehouse packet — {{ $batch->reference }}</title>
</head>

<body style="font-family: Arial, sans-serif; background: #f8fafc; color: #111827; padding: 24px;">
  <div style="max-width: 640px; margin: 0 auto; background: #13101e; border-radius: 12px 12px 0 0; padding: 24px 32px;">
    <span style="font-size: 20px; font-weight: bold; color: #e8d49a; letter-spacing: 0.05em;">ARCANE</span>
  </div>
  <div style="max-width: 640px; margin: 0 auto; background: #ffffff; border: 1px solid #e5e7eb; border-top: none; border-radius: 0 0 12px 12px; padding: 32px;">
    <h1 style="margin-top: 0; font-size: 22px;">Warehouse packet — {{ $batch->reference }}</h1>

    <p>Two PDFs are attached:</p>

    <ul style="padding-left: 20px; color: #374151;">
      <li><strong>Picking sheet</strong> — where to find each card, grouped by lot.</li>
      <li><strong>QR sheet</strong> — one QR code per pack, in the same order as the picking sheet, for sealing.</li>
    </ul>

    <p style="color: #6b7280; font-size: 13px;">
      Cards on the picking sheet are marked as picked the moment this was generated — a fresh run for this
      batch will only ever include whatever's still left.
    </p>
  </div>
</body>

</html>

<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>Credit Note {{ $creditNote->number }}</title>
</head>

<body style="font-family: Arial, sans-serif; background: #f8fafc; color: #111827; padding: 24px;">
  <div style="max-width: 640px; margin: 0 auto; background: #13101e; border-radius: 12px 12px 0 0; padding: 24px 32px;">
    <span style="font-size: 20px; font-weight: bold; color: #e8d49a; letter-spacing: 0.05em;">ARCANE</span>
  </div>
  <div style="max-width: 640px; margin: 0 auto; background: #ffffff; border: 1px solid #e5e7eb; border-top: none; border-radius: 0 0 12px 12px; padding: 32px;">
    <h1 style="margin-top: 0; font-size: 22px;">Credit Note {{ $creditNote->number }}</h1>

    <p>Hi {{ $creditNote->store->name }},</p>

    <p>
      We've issued you a credit note. The full details are attached to this email as a PDF.
    </p>

    <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
      <tr>
        <td style="padding: 8px 0; color: #6b7280;">Amount</td>
        <td style="padding: 8px 0; text-align: right; font-weight: bold; font-size: 18px; color: #c9a84c;">
          £{{ number_format($creditNote->amount_pence / 100, 2) }}
        </td>
      </tr>
      <tr>
        <td style="padding: 8px 0; color: #6b7280; border-top: 1px solid #e5e7eb;">Reason</td>
        <td style="padding: 8px 0; text-align: right; border-top: 1px solid #e5e7eb;">
          {{ $creditNote->reason }}
        </td>
      </tr>
    </table>

    <p>
      We'll either apply this against a future invoice or pay it back to you directly — you'll
      hear from us once that's settled, and it'll always be reflected on your account.
    </p>

    <p>
      If you have any questions about this credit note, just reply to this email.
    </p>

    <p>
      Best regards,<br>
      The Arcane Team
    </p>
  </div>
</body>

</html>

<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>Invoice {{ $invoice->number }}</title>
</head>

<body style="font-family: Arial, sans-serif; background: #f8fafc; color: #111827; padding: 24px;">
  <div style="max-width: 640px; margin: 0 auto; background: #13101e; border-radius: 12px 12px 0 0; padding: 24px 32px;">
    <span style="font-size: 20px; font-weight: bold; color: #e8d49a; letter-spacing: 0.05em;">ARCANE</span>
  </div>
  <div style="max-width: 640px; margin: 0 auto; background: #ffffff; border: 1px solid #e5e7eb; border-top: none; border-radius: 0 0 12px 12px; padding: 32px;">
    <h1 style="margin-top: 0; font-size: 22px;">Invoice {{ $invoice->number }}</h1>

    <p>Hi {{ $invoice->store->name }},</p>

    <p>
      Your invoice for {{ $invoice->batch ? 'batch '.$invoice->batch->reference : 'your Arcane order' }}
      is attached to this email as a PDF.
    </p>

    <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
      <tr>
        <td style="padding: 8px 0; color: #6b7280;">Amount due</td>
        <td style="padding: 8px 0; text-align: right; font-weight: bold; font-size: 18px; color: #c9a84c;">
          £{{ number_format($invoice->amount_due_pence / 100, 2) }}
        </td>
      </tr>
      <tr>
        <td style="padding: 8px 0; color: #6b7280; border-top: 1px solid #e5e7eb;">Due date</td>
        <td style="padding: 8px 0; text-align: right; border-top: 1px solid #e5e7eb;">
          {{ $invoice->due_on?->format('d M Y') ?? 'On receipt' }}
        </td>
      </tr>
    </table>

    <div style="background: #f5f2fa; border-radius: 8px; padding: 16px 20px; margin: 20px 0;">
      <div style="font-weight: bold; font-size: 13px; margin-bottom: 8px;">Payment details</div>
      <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
        <tr>
          <td style="padding: 2px 0; color: #6b7280; width: 40%;">Account name</td>
          <td style="padding: 2px 0;">ARCANE TCG LIMITED</td>
        </tr>
        <tr>
          <td style="padding: 2px 0; color: #6b7280;">Sort code</td>
          <td style="padding: 2px 0;">04-00-05</td>
        </tr>
        <tr>
          <td style="padding: 2px 0; color: #6b7280;">Account number</td>
          <td style="padding: 2px 0;">31710064</td>
        </tr>
        <tr>
          <td style="padding: 2px 0; color: #6b7280;">Reference</td>
          <td style="padding: 2px 0;">{{ $invoice->number }}</td>
        </tr>
      </table>
    </div>

    <p>
      If you have any questions about this invoice, just reply to this email.
    </p>

    <p>
      Best regards,<br>
      The Arcane Team
    </p>
  </div>
</body>

</html>

<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>New Arcane batch request</title>
</head>

<body style="font-family: Arial, sans-serif; background: #f8fafc; color: #111827; padding: 24px;">
  <div style="max-width: 640px; margin: 0 auto; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 32px;">
    <h1 style="margin-top: 0; font-size: 24px;">New batch request</h1>

    <p>A seller has requested a new Arcane batch.</p>

    <p><strong>Requested by:</strong> {{ $requestingUser->name }} ({{ $requestingUser->email }})</p>
    <p><strong>Store ID:</strong> {{ $batch->store_id }}</p>
    <p><strong>Batch reference:</strong> {{ $batch->reference }}</p>
    <p><strong>Game:</strong> {{ $batch->game?->label() ?? $batch->game }}</p>
    <p><strong>Product:</strong> {{ $batch->type?->label() ?? $batch->type }}</p>
    <p><strong>Packs:</strong> {{ $batch->pack_count }}</p>

    @if($batch->admin_notes)
    <p><strong>Notes:</strong></p>
    <p style="white-space: pre-line;">{{ $batch->admin_notes }}</p>
    @endif

    <p style="margin-top: 24px;">
      Review in admin:
      <a href="{{ url('/admin/batches') }}">{{ url('/admin/batches') }}</a>
    </p>
  </div>
</body>

</html>
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>{{ $batch->reference }} – QR Sheet</title>

  <style>
    @page {
      margin: 0;
    }

    html,
    body {
      margin: 0;
      padding: 0;
      font-family: DejaVu Sans, sans-serif;
      font-size: 6px;
    }

    .page {
      padding: 40px;
      box-sizing: border-box;
      overflow: hidden;
    }

    table {
      width: 714px;
      border-collapse: collapse;
      border-spacing: 0;
      table-layout: fixed;
    }

    .sheet-table td {
      width: 142.8px;
      min-width: 142.8px;
      max-width: 142.8px;
      height: 72px;
      padding: 1px;
      vertical-align: top;
      overflow: hidden;
    }

    .cell {
      border: 1px solid #fff;
      height: 70px;
      padding: 4px;
      overflow: hidden;
      text-align: center;
    }

    .qr {
      float: left;
      width: 52px;
      height: 52px;
      margin-bottom: 1px;
      margin-right: 5px;
      text-align: center;
      overflow: hidden;
    }

    .qr img {
      width: 52px;
      height: 52px;
      display: inline-block;
    }

    .meta {
      font-size: 8px;
      line-height: 1;
      overflow: hidden;
      text-align: left;
    }

    .name {
      font-weight: bold;
    }

    .muted {
      color: #444;
    }

    .new-page {
      page-break-before: always;
    }
  </style>
</head>

<body>
  @foreach ($rows->chunk(65) as $sheetRows)
  <div class="page {{ $loop->first ? '' : 'new-page' }}">
    <table cellspacing="0" cellpadding="0">
      <tbody>
        @foreach ($sheetRows->chunk(5) as $rowChunk)
        <tr>
          @foreach ($rowChunk as $row)
          <td>
            <div class="cell">
              <div class="qr">
                @if (!empty($row['qr_png']))
                <img src="{{ $row['qr_png'] }}" alt="QR code">
                @else
                <div class="no-qr">No QR</div>
                @endif
              </div>

              <div class="meta">
                <strong>#{{ $row['sequence'] }}</strong><br><span class="name">{{ \Illuminate\Support\Str::limit($row['name'], 14, '') }}</span><br>@if (!empty($row['number']))<span class="muted">{{ $row['number'] }}</span><br>@endif<span class="muted">Band: {{ ucfirst($row['band'] ?: 'n/a') }}</span>
              </div>
            </div>
          </td>
          @endforeach
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endforeach
</body>

</html>
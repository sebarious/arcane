<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>{{ $batch->reference }} – QR Sheet</title>

  <style>
    @page {
      size: A4 portrait;
      margin: 10.7mm 12.5mm;
    }

    * {
      box-sizing: border-box;
    }

    html,
    body {
      margin: 0;
      padding: 0;
      font-family: DejaVu Sans, sans-serif;
      font-size: 6px;
    }

    .sheet {
      width: 185mm;
      height: 275.6mm;
      page-break-after: always;
    }

    .sheet:last-child {
      page-break-after: auto;
    }

    table.labels {
      width: 185mm;
      height: 275.6mm;
      border-collapse: collapse;
      table-layout: fixed;
    }

    table.labels td {
      width: 37mm;
      height: 21.2mm;
      padding: 0;
      margin: 0;
      vertical-align: top;
      overflow: hidden;
    }

    .label {
      width: 37mm;
      height: 21.2mm;
      padding: 1.4mm;
      overflow: hidden;

      /*
             * Enable this temporarily for test prints:
             * border: 0.2mm solid #ccc;
             */
    }

    .qr {
      float: left;
      width: 15mm;
      height: 15mm;
      margin-right: 1.3mm;
      text-align: center;
    }

    .qr img {
      width: 15mm;
      height: 15mm;
      display: block;
    }

    .no-qr {
      width: 15mm;
      height: 15mm;
      line-height: 15mm;
      text-align: center;
      font-size: 5px;
      border: 0.2mm solid #999;
    }

    .meta {
      font-size: 5.5px;
      line-height: 1.15;
      overflow: hidden;
    }

    .meta strong {
      font-size: 6.5px;
    }

    .name {
      font-weight: bold;
    }

    .muted {
      color: #444;
    }
  </style>
</head>

<body>
  @foreach ($rows->chunk(65) as $sheetRows)
  <div class="sheet">
    <table class="labels">
      <tbody>
        @foreach ($sheetRows->chunk(5) as $rowChunk)
        <tr>
          @foreach ($rowChunk as $row)
          <td>
            <div class="label">
              <div class="qr">
                @if (!empty($row['qr_png']))
                <img src="{{ $row['qr_png'] }}" alt="QR code">
                @else
                <div class="no-qr">No QR</div>
                @endif
              </div>

              <div class="meta">
                <strong>#{{ $row['sequence'] }}</strong><br>
                <span class="name">{{ $row['name'] }}</span><br>
                <span class="muted">
                  {{ $row['set'] }}
                  @if (!empty($row['number']))
                  · {{ $row['number'] }}
                  @endif
                </span><br>
                <span class="muted">
                  Band: {{ ucfirst($row['band'] ?: 'n/a') }}
                </span>
              </div>
            </div>
          </td>
          @endforeach

          {{-- Pad final row so table keeps 5 columns --}}
          @for ($i = $rowChunk->count(); $i < 5; $i++)
            <td>
            <div class="label"></div>
            </td>
            @endfor
        </tr>
        @endforeach

        {{-- Pad final sheet so table keeps 13 rows --}}
        @for ($i = $sheetRows->chunk(5)->count(); $i < 13; $i++)
          <tr>
          @for ($j = 0; $j < 5; $j++)
            <td>
            <div class="label"></div>
            </td>
            @endfor
            </tr>
            @endfor
      </tbody>
    </table>
  </div>
  @endforeach
</body>

</html>
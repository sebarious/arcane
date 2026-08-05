<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Arcane – Scan cards</title>
  <style>
    * { box-sizing: border-box; }

    body {
      font-family: system-ui, -apple-system, sans-serif;
      margin: 0;
      padding: 1rem;
      background: #0a0a0f;
      color: #e5e7eb;
      min-height: 100vh;
    }

    h1 {
      font-size: 1.1rem;
      margin: 0 0 0.25rem;
    }

    p.hint {
      margin: 0 0 1rem;
      color: #9ca3af;
      font-size: 0.85rem;
    }

    .video-wrap {
      position: relative;
      width: 100%;
      max-width: 22rem;
      margin-left: auto;
      margin-right: auto;
      aspect-ratio: 3 / 4;
      background: #000;
      border-radius: 0.75rem;
      overflow: hidden;
      margin-bottom: 1rem;
    }

    video {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: none;
    }

    .placeholder {
      position: absolute;
      inset: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #6b7280;
      font-size: 0.85rem;
      text-align: center;
      padding: 1rem;
    }

    button {
      font-size: 1rem;
      font-weight: 600;
      padding: 0.9rem 1.25rem;
      border-radius: 0.6rem;
      border: none;
      width: 100%;
      cursor: pointer;
    }

    button.start { background: #7c3aed; color: white; }
    button.stop { background: #1f2937; color: #e5e7eb; border: 1px solid #374151; }

    .status {
      margin-top: 1rem;
      font-size: 1rem;
      font-weight: 600;
      text-align: center;
      min-height: 1.4em;
    }

    .status.gray { color: #9ca3af; }
    .status.success { color: #4ade80; }
    .status.warning { color: #fbbf24; }
    .status.danger { color: #f87171; }

    .count {
      text-align: center;
      color: #9ca3af;
      font-size: 0.85rem;
      margin-top: 0.5rem;
    }
  </style>
</head>

<body>
  <h1>Scan cards</h1>
  <p class="hint">Hold each card's number up to the camera and move to the next once it's added. Results show up on your computer automatically.</p>

  <div class="video-wrap">
    <video id="video" autoplay playsinline muted></video>
    <div id="placeholder" class="placeholder">Camera preview appears here once scanning starts.</div>
  </div>
  <canvas id="canvas" style="display:none"></canvas>

  <button id="startBtn" class="start">Start scanning</button>
  <button id="stopBtn" class="stop" style="display:none">Stop scanning</button>

  <p id="status" class="status gray">Idle — press Start scanning</p>
  <p class="count">Added this session: <span id="count">0</span></p>

  <script>
    (function () {
      const token = @json($token);
      const frameUrl = @json(route('rapid-intake.scan.frame', $token));
      const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

      const video = document.getElementById('video');
      const placeholder = document.getElementById('placeholder');
      const canvas = document.getElementById('canvas');
      const startBtn = document.getElementById('startBtn');
      const stopBtn = document.getElementById('stopBtn');
      const statusEl = document.getElementById('status');
      const countEl = document.getElementById('count');

      let stream = null;
      let active = false;
      let busy = false;
      let intervalId = null;
      let addedCount = 0;
      let flashTimeout = null;

      function setStatus(text, color) {
        statusEl.textContent = text;
        statusEl.className = 'status ' + color;
      }

      function flash(text, color) {
        setStatus(text, color);
        clearTimeout(flashTimeout);
        flashTimeout = setTimeout(() => {
          if (active) setStatus('Scanning…', 'gray');
        }, 2500);
      }

      async function start() {
        try {
          // Cards are portrait-shaped — bias the stream toward portrait so the
          // preview box (also portrait, see CSS) doesn't crop the card's edges.
          stream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'environment', width: { ideal: 720 }, height: { ideal: 1280 } },
          });
        } catch (e) {
          setStatus('Camera access denied or unavailable.', 'danger');
          return;
        }

        video.srcObject = stream;
        video.style.display = 'block';
        placeholder.style.display = 'none';
        active = true;
        startBtn.style.display = 'none';
        stopBtn.style.display = 'block';
        setStatus('Scanning…', 'gray');
        intervalId = setInterval(captureAndScan, 1500);
      }

      function stop() {
        active = false;
        if (intervalId) clearInterval(intervalId);
        if (stream) {
          stream.getTracks().forEach((t) => t.stop());
          stream = null;
        }
        video.style.display = 'none';
        placeholder.style.display = 'flex';
        startBtn.style.display = 'block';
        stopBtn.style.display = 'none';
        setStatus('Idle — press Start scanning', 'gray');
      }

      async function captureAndScan() {
        if (busy || !active || !video.videoWidth) return;
        busy = true;

        const maxWidth = 900;
        const scale = Math.min(1, maxWidth / video.videoWidth);
        canvas.width = video.videoWidth * scale;
        canvas.height = video.videoHeight * scale;
        canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
        const dataUrl = canvas.toDataURL('image/jpeg', 0.7);

        try {
          const response = await fetch(frameUrl, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ image: dataUrl }),
          });

          if (response.status === 410) {
            flash('Scan session expired — refresh from your computer.', 'danger');
            stop();
            return;
          }

          const result = await response.json();
          handleResult(result);
        } catch (e) {
          flash('Scan failed — retrying…', 'danger');
        } finally {
          busy = false;
        }
      }

      function handleResult(result) {
        switch (result.status) {
          case 'resolved':
            addedCount++;
            countEl.textContent = addedCount;
            flash('✓ Added: ' + (result.card_name || result.number), 'success');
            break;
          case 'ambiguous':
            addedCount++;
            countEl.textContent = addedCount;
            flash('⚠ ' + result.number + ' — multiple matches, resolve on your computer', 'warning');
            break;
          case 'not_found':
            flash('✕ ' + result.number + ' — no match, nothing added. Set aside or try again.', 'warning');
            break;
          case 'duplicate':
            break;
          case 'limit_reached':
            flash('Row limit reached — scanning stopped.', 'warning');
            stop();
            break;
          case 'no_number':
          case 'no_text':
            setStatus('Scanning…', 'gray');
            break;
          case 'error':
            flash(result.message || 'Scan error', 'danger');
            break;
        }
      }

      startBtn.addEventListener('click', start);
      stopBtn.addEventListener('click', stop);
      window.addEventListener('beforeunload', stop);
    })();
  </script>
</body>

</html>

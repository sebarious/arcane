<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Arcane – Photograph a card</title>
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

    h1 { font-size: 1.1rem; margin: 0 0 0.25rem; }

    p.hint { margin: 0 0 1rem; color: #9ca3af; font-size: 0.85rem; }

    .frame {
      position: relative;
      width: 100%;
      max-width: 22rem;
      margin: 0 auto 1rem;
      aspect-ratio: 3 / 4;
      background: #000;
      border-radius: 0.75rem;
      overflow: hidden;
    }

    video, img.shot { width: 100%; height: 100%; object-fit: cover; display: block; }

    .row { display: flex; gap: 0.75rem; max-width: 22rem; margin: 0 auto; }

    button {
      flex: 1;
      padding: 0.9rem 1rem;
      font-size: 1rem;
      font-weight: 600;
      border-radius: 0.6rem;
      border: 0;
      cursor: pointer;
    }

    button.primary { background: #c9a84c; color: #0a0a0f; }
    button.secondary { background: transparent; color: #e5e7eb; border: 1px solid #3f3f52; }
    button[disabled] { opacity: 0.5; }

    .status {
      max-width: 22rem;
      margin: 1rem auto 0;
      padding: 0.75rem;
      border-radius: 0.5rem;
      font-size: 0.9rem;
      text-align: center;
    }

    .status.ok { background: rgba(34, 197, 94, 0.15); color: #4ade80; }
    .status.err { background: rgba(239, 68, 68, 0.15); color: #f87171; }
    .hidden { display: none; }
  </style>
</head>

<body>
  <h1>Photograph this card</h1>
  <p class="hint">Fill the frame with the card or slab, then tap Capture. It appears on the computer automatically.</p>

  <div class="frame">
    <video id="video" autoplay playsinline muted></video>
    <img id="shot" class="shot hidden" alt="">
  </div>

  <div class="row" id="liveControls">
    <button type="button" class="primary" id="capture">Capture</button>
  </div>

  <div class="row hidden" id="reviewControls">
    <button type="button" class="secondary" id="retake">Retake</button>
    <button type="button" class="primary" id="use">Use this photo</button>
  </div>

  <div id="status" class="status hidden"></div>

  <canvas id="canvas" class="hidden"></canvas>

  <script>
    const video = document.getElementById('video');
    const shot = document.getElementById('shot');
    const canvas = document.getElementById('canvas');
    const statusEl = document.getElementById('status');
    const liveControls = document.getElementById('liveControls');
    const reviewControls = document.getElementById('reviewControls');

    let stream = null;
    let pending = null;

    function status(message, kind) {
      statusEl.textContent = message;
      statusEl.className = 'status ' + kind;
    }

    async function start() {
      try {
        stream = await navigator.mediaDevices.getUserMedia({
          video: { facingMode: 'environment', width: { ideal: 1080 }, height: { ideal: 1440 } },
        });
      } catch (e) {
        status('Camera unavailable — check this page has camera permission.', 'err');
        return;
      }

      video.srcObject = stream;
    }

    document.getElementById('capture').addEventListener('click', () => {
      if (!video.videoWidth) return;

      // Cap the long edge — a full-resolution phone frame is far larger than
      // a catalogue image needs and slow to post over mobile data.
      const maxWidth = 1400;
      const scale = Math.min(1, maxWidth / video.videoWidth);
      canvas.width = video.videoWidth * scale;
      canvas.height = video.videoHeight * scale;
      canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);

      pending = canvas.toDataURL('image/jpeg', 0.85);
      shot.src = pending;

      // Review before sending — a blurred or half-cropped card is the whole
      // reason to do this on a phone rather than trusting one blind shot.
      video.classList.add('hidden');
      shot.classList.remove('hidden');
      liveControls.classList.add('hidden');
      reviewControls.classList.remove('hidden');
      statusEl.className = 'status hidden';
    });

    document.getElementById('retake').addEventListener('click', () => {
      pending = null;
      shot.classList.add('hidden');
      video.classList.remove('hidden');
      reviewControls.classList.add('hidden');
      liveControls.classList.remove('hidden');
      statusEl.className = 'status hidden';
    });

    document.getElementById('use').addEventListener('click', async (event) => {
      if (!pending) return;

      event.target.disabled = true;
      status('Sending…', 'ok');

      try {
        const response = await fetch(@json(route('card-photo.store', $token)), {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          },
          body: JSON.stringify({ image: pending }),
        });

        if (response.status === 410) {
          status('That session has expired — start it again on the computer.', 'err');
          return;
        }

        if (!response.ok) {
          status('Could not send that photo — try again.', 'err');
          event.target.disabled = false;
          return;
        }

        status('Sent. It should appear on the computer now — you can close this page.', 'ok');
        reviewControls.classList.add('hidden');

        if (stream) stream.getTracks().forEach((t) => t.stop());
      } catch (e) {
        status('Could not send that photo — check your connection and try again.', 'err');
        event.target.disabled = false;
      }
    });

    start();
  </script>
</body>

</html>

<?php

namespace App\Filament\Resources\CardInventories\Concerns;

use App\Services\Intake\CardPhotoSession;
use Filament\Notifications\Notification;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * Desktop half of "photograph this card with your phone": mints a pairing
 * session, hands back a QR code for it, and polls until the phone has
 * uploaded something.
 *
 * Mirrors Rapid Intake's "Scan with phone" handoff (see ScanSession and
 * PhoneScanController) — the admin machine is usually a desktop with no
 * usable camera, and the card is in someone's hand at the counter.
 *
 * Shared by the create and edit pages; both offer the camera.
 */
trait StoresCapturedCardPhoto
{
    public ?string $cardPhotoToken = null;

    /**
     * Starts a session and returns what the field needs to render the code.
     *
     * @return array{url: string, svg: string}
     */
    public function startCardPhotoSession(): array
    {
        $sessions = app(CardPhotoSession::class);

        if (! $this->cardPhotoToken || ! $sessions->exists($this->cardPhotoToken)) {
            $this->cardPhotoToken = $sessions->create();
        }

        $url = route('card-photo.show', $this->cardPhotoToken);

        // Cast, don't pass through: generate() hands back an HtmlString, which
        // json_encodes to {} and reaches the browser as "[object Object]".
        // Rapid Intake gets away with the object because it renders server
        // side via Blade ({!! $svg !!}); this crosses the Livewire JSON
        // boundary, so it has to be a real string.
        //
        // The XML prolog goes too — harmless in a Blade-rendered document,
        // but pointless noise when the markup is injected with x-html.
        $svg = (string) QrCode::format('svg')->size(200)->margin(1)->generate($url);
        $svg = preg_replace('/^<\?xml[^>]*\?>\s*/', '', $svg);

        return [
            'url' => $url,
            'svg' => $svg,
        ];
    }

    /**
     * Polled by the field while a session is open. Returns the stored path
     * once the phone has sent a photo, so the field can show it immediately.
     */
    public function pollCardPhoto(): ?string
    {
        if (! $this->cardPhotoToken) {
            return null;
        }

        $sessions = app(CardPhotoSession::class);

        if (! $sessions->exists($this->cardPhotoToken)) {
            $this->cardPhotoToken = null;

            return null;
        }

        $path = $sessions->path($this->cardPhotoToken);

        if (! $path) {
            return null;
        }

        // Into form state, not the record — the photo is only committed when
        // the form itself is saved, so backing out of the page leaves the card
        // as it was (the file is orphaned, same as abandoning a normal upload).
        $this->data['custom_image_path'] = $path;

        $sessions->forget($this->cardPhotoToken);
        $this->cardPhotoToken = null;

        Notification::make()
            ->title('Photo received')
            ->body('It replaces the stock artwork for this card once you save.')
            ->success()
            ->send();

        return $path;
    }

    public function endCardPhotoSession(): void
    {
        if ($this->cardPhotoToken) {
            app(CardPhotoSession::class)->forget($this->cardPhotoToken);
        }

        $this->cardPhotoToken = null;
    }
}

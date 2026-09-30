<?php

namespace App\Filament\Resources\CardInventories\Concerns;

use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Receives a still from the card-photo camera field (see
 * resources/views/filament/forms/components/card-photo-capture.blade.php)
 * and writes it to the same disk and directory the ordinary file upload uses,
 * so from the form's point of view the two routes are indistinguishable.
 *
 * Shared by the create and edit pages — both offer the camera.
 */
trait StoresCapturedCardPhoto
{
    public function storeCapturedCardPhoto(string $dataUrl): void
    {
        // data:image/jpeg;base64,xxxx — anything else didn't come from the
        // capture field, so refuse rather than trying to interpret it.
        if (! preg_match('/^data:image\/(jpeg|png|webp);base64,/', $dataUrl, $matches)) {
            Notification::make()
                ->title('That photo could not be read')
                ->danger()
                ->send();

            return;
        }

        $binary = base64_decode(substr($dataUrl, strpos($dataUrl, ',') + 1), true);

        if ($binary === false) {
            Notification::make()
                ->title('That photo could not be read')
                ->danger()
                ->send();

            return;
        }

        $path = 'card-photos/'.Str::uuid().'.'.($matches[1] === 'jpeg' ? 'jpg' : $matches[1]);

        Storage::disk('public')->put($path, $binary);

        // Straight into the form's state, not the record — the photo is only
        // committed when the form itself is saved, so backing out of the page
        // leaves the card as it was (the file is orphaned, which is the same
        // outcome as abandoning a normal upload).
        $this->data['custom_image_path'] = $path;

        Notification::make()
            ->title('Photo captured')
            ->body('It replaces the stock artwork for this card once you save.')
            ->success()
            ->send();
    }
}

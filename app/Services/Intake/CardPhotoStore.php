<?php

namespace App\Services\Intake;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Turns a canvas data URL from a phone's camera into a stored file, on the
 * same disk and directory the admin's ordinary file upload writes to — so a
 * photo's origin makes no difference to anything downstream.
 */
class CardPhotoStore
{
    /** @return string|null the stored path, or null if the payload wasn't a readable image */
    public function storeDataUrl(string $dataUrl): ?string
    {
        if (! preg_match('/^data:image\/(jpeg|png|webp);base64,/', $dataUrl, $matches)) {
            return null;
        }

        $binary = base64_decode(substr($dataUrl, strpos($dataUrl, ',') + 1), true);

        if ($binary === false || $binary === '') {
            return null;
        }

        $path = 'card-photos/'.Str::uuid().'.'.($matches[1] === 'jpeg' ? 'jpg' : $matches[1]);

        Storage::disk('public')->put($path, $binary);

        return $path;
    }
}

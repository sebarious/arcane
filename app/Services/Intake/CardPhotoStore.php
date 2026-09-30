<?php

namespace App\Services\Intake;

use Illuminate\Http\UploadedFile;

/**
 * One place that decides where card photos live, so a photo's origin — the
 * admin's own file upload, or one sent from a phone — makes no difference to
 * anything downstream.
 */
class CardPhotoStore
{
    /** @return string the stored path on the public disk */
    public function storeUpload(UploadedFile $file): string
    {
        return $file->store('card-photos', 'public');
    }
}

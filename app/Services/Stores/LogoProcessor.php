<?php

namespace App\Services\Stores;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\ImageManager;

class LogoProcessor
{
    /**
     * Shrinks (never upscales — scaleDown is a no-op below this size) and
     * re-encodes an uploaded logo to PNG before storing it, then saves to the
     * 'local' disk — the same disk ImageController reads from via
     * Store::getLogoAttribute(). Store::update() elsewhere saved logos to the
     * 'public' disk instead, a mismatch that made every seller-uploaded logo
     * 404 when displayed; this keeps every logo write on the disk that's
     * actually served.
     *
     * @return string The stored path, e.g. "store-logos/abc123.png".
     */
    public function process(UploadedFile $file, string $directory = 'store-logos'): string
    {
        $manager = new ImageManager(Driver::class);

        $image = $manager->decodePath($file->getRealPath());
        $image->scaleDown(width: 800, height: 800);

        $path = trim($directory, '/').'/'.Str::random(32).'.png';

        Storage::disk('local')->put($path, (string) $image->encode(new PngEncoder()));

        return $path;
    }
}

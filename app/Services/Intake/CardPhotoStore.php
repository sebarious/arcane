<?php

namespace App\Services\Intake;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * One place that decides where card photos live, so a photo's origin — the
 * admin's own file upload, or one sent from a phone — makes no difference to
 * anything downstream.
 */
class CardPhotoStore
{
    public const DIRECTORY = 'card-photos';

    /**
     * @return string|null the stored path, or null if the write failed
     *
     * Nullable on purpose. The 'public' disk is configured with
     * 'throw' => false, so a failed write comes back as false rather than an
     * exception — and with a `: string` return type PHP quietly coerced that
     * to '', which read as success all the way back to the phone while the
     * photo went nowhere. A failure has to be visible.
     */
    public function storeUpload(UploadedFile $file): ?string
    {
        $path = $file->store(self::DIRECTORY, 'public');

        return ($path === false || $path === '') ? null : $path;
    }

    /**
     * Why a write would have failed, for the log — the disk swallows the real
     * reason, and it is almost always the directory's permissions on a fresh
     * deploy rather than anything about the file.
     *
     * @return array<string, mixed>
     */
    public function diagnostics(): array
    {
        $root = Storage::disk('public')->path(self::DIRECTORY);

        return [
            'directory' => $root,
            'exists' => is_dir($root),
            'writable' => is_dir($root) ? is_writable($root) : null,
            'parent_writable' => is_writable(dirname($root)),
            'free_bytes' => @disk_free_space(dirname($root)) ?: null,
        ];
    }
}

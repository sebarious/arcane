<?php

namespace App\Services\Vision;

/**
 * Some mobile browsers hand canvas.drawImage() the camera's raw sensor buffer,
 * which can be rotated 90°/180°/270° from what's actually shown on screen — a
 * known, long-standing cross-browser inconsistency, not something fixable from
 * CSS or a capture-time orientation guess alone (there's no reliable way to
 * detect which browsers are affected or which direction they're off by without
 * testing on the specific device). Rather than guess, this tries the frame as
 * captured first, then each rotation in turn, until one actually yields a
 * readable card number — server-side, so it works the same regardless of the
 * phone/browser doing the capturing.
 */
class RotatingFrameScanner
{
    private const ROTATIONS = [0, 90, 270, 180];

    public function __construct(
        protected GoogleVisionClient $vision,
    ) {}

    /**
     * @param  int|null  $preferredRotation  Try this rotation first — once a
     *         scanning session learns which rotation a device needs, passing it
     *         back in avoids re-sweeping all four on every subsequent frame.
     * @return array{number: ?string, name: ?string, rotation: ?int}
     */
    public function scan(string $bytes, ?int $preferredRotation = null): array
    {
        $order = self::ROTATIONS;

        if ($preferredRotation !== null && in_array($preferredRotation, $order, true)) {
            $order = [$preferredRotation, ...array_diff($order, [$preferredRotation])];
        }

        foreach ($order as $degrees) {
            $frame = $degrees === 0 ? $bytes : self::rotate($bytes, $degrees);
            if ($frame === null) {
                continue;
            }

            $result = $this->vision->detect($frame);
            if (blank($result['text'])) {
                continue;
            }

            $number = CardNumberExtractor::extract($result['text']);
            if ($number) {
                return [
                    'number'   => $number,
                    'name'     => CardNameExtractor::extract($result['annotations']),
                    'rotation' => $degrees,
                ];
            }
        }

        return ['number' => null, 'name' => null, 'rotation' => null];
    }

    private static function rotate(string $bytes, int $degrees): ?string
    {
        $image = @imagecreatefromstring($bytes);
        if ($image === false) {
            return null;
        }

        $rotated = imagerotate($image, $degrees, 0);
        imagedestroy($image);

        if ($rotated === false) {
            return null;
        }

        ob_start();
        imagejpeg($rotated, quality: 85);
        $output = ob_get_clean();
        imagedestroy($rotated);

        return $output ?: null;
    }
}

<?php

namespace App\Services\Vision;

use Illuminate\Support\Facades\Http;

class GoogleVisionClient
{
    public function __construct(
        protected ?string $apiKey,
    ) {}

    /**
     * OCR a single image and return the full detected text block, or null if
     * nothing readable was found (or the request failed — this is best-effort,
     * called on a background capture loop, so a transient failure just means
     * "try again on the next frame" rather than something worth throwing over).
     *
     * @param  string  $imageContents  Raw image bytes (not base64-encoded yet).
     */
    public function detectText(string $imageContents): ?string
    {
        if (blank($this->apiKey)) {
            throw new \RuntimeException('GOOGLE_VISION_API_KEY is not configured.');
        }

        $response = Http::baseUrl('https://vision.googleapis.com/v1')
            ->timeout(10)
            ->retry(1, 200, throw: false)
            ->post('/images:annotate?key='.$this->apiKey, [
                'requests' => [
                    [
                        'image' => [
                            'content' => base64_encode($imageContents),
                        ],
                        'features' => [
                            ['type' => 'TEXT_DETECTION', 'maxResults' => 1],
                        ],
                    ],
                ],
            ]);

        if ($response->failed()) {
            return null;
        }

        $result = $response->json('responses.0');

        if (isset($result['error'])) {
            return null;
        }

        return $result['fullTextAnnotation']['text']
            ?? $result['textAnnotations'][0]['description']
            ?? null;
    }
}

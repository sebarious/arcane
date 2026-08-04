<?php

namespace App\Services\Vision;

use Illuminate\Support\Facades\Http;

class GoogleVisionClient
{
    public function __construct(
        protected ?string $apiKey,
    ) {}

    /**
     * OCR a single image and return both the flattened text block (used for the
     * card number regex) and Vision's raw per-word `textAnnotations`, each with
     * its own pixel bounding box (used to find the topmost line for the card
     * name — see CardNameExtractor). One Vision call gives us both, so this
     * deliberately isn't split into two methods that would double the request.
     *
     * @return array{text: ?string, annotations: array}
     */
    public function detect(string $imageContents): array
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
                            ['type' => 'TEXT_DETECTION'],
                        ],
                    ],
                ],
            ]);

        if ($response->failed()) {
            return ['text' => null, 'annotations' => []];
        }

        $result = $response->json('responses.0');

        if (isset($result['error'])) {
            return ['text' => null, 'annotations' => []];
        }

        return [
            'text'        => $result['fullTextAnnotation']['text']
                ?? $result['textAnnotations'][0]['description']
                ?? null,
            'annotations' => $result['textAnnotations'] ?? [],
        ];
    }
}

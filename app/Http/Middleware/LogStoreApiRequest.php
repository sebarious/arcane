<?php

namespace App\Http\Middleware;

use App\Models\ApiRequestLog;
use App\Models\Store;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Records every partner API request for admin review (StoreResource's "API
 * logs" tab) — the main tool for approving a seller's integration before
 * switching them to live or enabling markAsSold. Runs via terminate() so
 * logging never adds latency to the response the partner sees.
 */
class LogStoreApiRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->attributes->set('api_log_started_at', microtime(true));

        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        $startedAt = $request->attributes->get('api_log_started_at', microtime(true));
        /** @var Store|null $store */
        $store = $request->attributes->get('api_store');

        ApiRequestLog::create([
            'store_id' => $store?->id,
            'method' => $request->method(),
            'path' => '/'.$request->path(),
            'status_code' => $response->getStatusCode(),
            'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
            'ip' => $request->ip(),
            'response_summary' => $this->summarize($response),
            'created_at' => now(),
        ]);
    }

    private function summarize(Response $response): ?array
    {
        $content = $response->getContent();

        if (! $content) {
            return null;
        }

        $decoded = json_decode($content, true);

        if (! is_array($decoded)) {
            return null;
        }

        // A packs list can run to 100+ rows — keep the log digestible with a
        // count instead of dumping every pack into every row.
        if (isset($decoded['data']) && is_array($decoded['data']) && array_is_list($decoded['data']) && count($decoded['data']) > 5) {
            return ['data_count' => count($decoded['data'])];
        }

        return $decoded;
    }
}

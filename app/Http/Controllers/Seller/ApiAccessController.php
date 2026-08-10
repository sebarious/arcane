<?php

namespace App\Http\Controllers\Seller;

use App\Enums\ApiMode;
use App\Http\Controllers\Controller;
use App\Models\ApiDailyUsage;
use App\Models\ApiSetting;
use App\Models\Store;
use App\Services\Api\SandboxBatchProvisioner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ApiAccessController extends Controller
{
    public function show(Request $request, SandboxBatchProvisioner $sandboxProvisioner): Response
    {
        $user = $request->user();
        $stores = $user->stores()->get(['id', 'name']);

        $activeStoreId = $request->integer('store') ?: $stores->first()?->id;
        $store = $user->stores()->findOrFail($activeStoreId);
        $settings = ApiSetting::current();

        return Inertia::render('Seller/ApiAccess', [
            'stores' => $stores,
            'store' => [
                'id' => $store->id,
                'slug' => $store->slug,
                'name' => $store->name,
            ],
            'api' => [
                'access_granted' => $store->api_access_granted,
                'enabled' => $store->api_enabled,
                'token' => $store->api_token,
                'mode' => $store->api_mode->value,
                'mark_as_sold_enabled' => $store->mark_as_sold_enabled,
            ],
            'sandbox' => $store->api_mode === ApiMode::Test && $store->api_enabled
                ? ['reference' => $sandboxProvisioner->ensure($store)->reference]
                : null,
            'limits' => [
                'rate_limit_per_minute' => $settings->rate_limit_per_minute,
                'daily_request_limit' => $store->daily_request_limit,
            ],
            'usage' => [
                'used_today' => ApiDailyUsage::usedToday($store),
                'last_30_days' => ApiDailyUsage::trailing($store, 30),
            ],
        ]);
    }

    public function toggle(Request $request, Store $store): RedirectResponse
    {
        $this->authorizeStore($request, $store);

        if (! $store->api_access_granted) {
            abort(403, 'API access has not been granted for this store yet.');
        }

        $data = $request->validate(['enabled' => ['required', 'boolean']]);

        $store->update(['api_enabled' => $data['enabled']]);

        return back()->with('success', $data['enabled'] ? 'API access enabled.' : 'API access disabled.');
    }

    public function regenerate(Request $request, Store $store): RedirectResponse
    {
        $this->authorizeStore($request, $store);

        if (! $store->api_access_granted) {
            abort(403, 'API access has not been granted for this store yet.');
        }

        $store->regenerateApiToken();

        return back()->with('success', 'A new API token has been generated. Update any integrations using the old one.');
    }

    /** Resets the store's sandbox batch (test mode only) back to unsold, so a seller can retest without waiting on us. */
    public function regenerateSandbox(Request $request, Store $store, SandboxBatchProvisioner $sandboxProvisioner): RedirectResponse
    {
        $this->authorizeStore($request, $store);

        if ($store->api_mode !== ApiMode::Test) {
            abort(403, 'Sandbox reset is only available while in test mode.');
        }

        $sandboxProvisioner->reset($store);

        return back()->with('success', 'Sandbox batch reset — every pack is unsold again.');
    }

    private function authorizeStore(Request $request, Store $store): void
    {
        if (! $request->user()->stores()->where('id', $store->id)->exists()) {
            abort(403);
        }
    }
}

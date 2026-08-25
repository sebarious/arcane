<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Services\Stores\LogoProcessor;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        $stores = $user->stores()->get(['id', 'name']);

        $activeStoreId = $request->integer('store') ?: $stores->first()?->id;
        $store = $user->stores()->findOrFail($activeStoreId);

        return Inertia::render('Seller/Profile', [
            'stores' => $stores,
            'store'  => [
                'id'           => $store->id,
                'name'         => $store->name,
                'slug'         => $store->slug,
                'description'  => $store->description,
                'location'     => $store->location,
                'logo'         => $store->logo,
                'social_links' => (object) ($store->social_links ?? []),
            ],
        ]);
    }

    public function update(Request $request, Store $store, LogoProcessor $logoProcessor)
    {
        $user = $request->user();
        if (! $user->stores()->where('id', $store->id)->exists()) {
            abort(403);
        }

        $data = $request->validate([
            'description'  => ['nullable', 'string', 'max:2000'],
            'location'     => ['nullable', 'string', 'max:255'],
            // See OnboardingController's identical rule for why this isn't 2048 —
            // LogoProcessor shrinks the stored file well under 2MB regardless of
            // upload size, so the raw ceiling just needs to clear a phone photo.
            'logo'         => ['nullable', 'image', 'max:10240'],
            'social_links' => ['nullable', 'array'],
            'social_links.*' => ['nullable', 'url', 'max:2048'],
        ]);

        if ($request->hasFile('logo')) {
            $data['logo'] = $logoProcessor->process($request->file('logo'));
        }

        $store->update([
            'description'  => $data['description'] ?? null,
            'location'     => $data['location'] ?? null,
            'social_links' => array_filter($data['social_links'] ?? []),
            ...(isset($data['logo']) ? ['logo' => $data['logo']] : []),
        ]);

        return back()->with('success', 'Your store profile has been updated.');
    }
}

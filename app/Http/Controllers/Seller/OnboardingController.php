<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Mail\SellerOnboardingReceivedMail;
use App\Mail\SellerOnboardingSubmittedMail;
use App\Models\Store;
use App\Models\User;
use App\Services\Stores\LogoProcessor;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

/**
 * The one page reachable pre-store.live (see EnsureSellerStoreIsPublic) — a
 * seller lands here right after approval to submit their onboarding details
 * (bio, location, platforms, social links, logo), then sees a "submitted,
 * under review" holding state until an admin approves (StoreResource::
 * approveOnboardingAction()) and public_page_enabled flips.
 */
class OnboardingController extends Controller
{
    private const PLATFORMS = [
        'physical_store', 'ebay', 'cardmarket', 'whatnot', 'instagram', 'tiktok_shop', 'website',
    ];

    private const SOCIAL_FIELDS = [
        'website', 'instagram', 'tiktok', 'youtube', 'x', 'facebook', 'discord',
    ];

    public function show(Request $request)
    {
        $store = $request->user()->store;

        return Inertia::render('Seller/Onboarding', [
            'affiliateCode' => $store?->affiliate_code,
            'bonusPercentage' => (float) config('selling.affiliate_bonus_percentage', 0.05),
            'submitted' => (bool) $store?->onboarding_submitted_at,
            'store' => $store ? [
                'name' => $store->name,
                'description' => $store->description,
                'location' => $store->location,
                'logo' => $store->logo,
                'platforms' => collect($store->platforms ?? [])
                    ->filter(fn ($enabled) => (bool) $enabled)
                    ->keys()
                    ->values(),
                'social_links' => (object) ($store->social_links ?? []),
            ] : null,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $store = $user->store;

        if (! $store) {
            abort(404);
        }

        $data = $request->validate([
            'description' => ['required', 'string', 'max:2000'],
            'location' => ['required', 'string', 'max:255'],
            // The 2MB figure people are told is what we keep, not what we accept —
            // LogoProcessor shrinks whatever comes in down to well under that
            // regardless of source size, so the raw ceiling here just needs to
            // clear a normal phone photo (matches public/.user.ini's raised
            // upload_max_filesize, which would otherwise silently drop the file
            // before Laravel — let alone LogoProcessor — ever sees it).
            'logo' => ['nullable', 'image', 'max:10240'],
            'platforms' => ['nullable', 'array'],
            'platforms.*' => ['string', 'in:'.implode(',', self::PLATFORMS)],
            'social_links' => ['nullable', 'array'],
            'social_links.*' => ['nullable', 'url', 'max:2048'],
        ]);

        $platforms = collect(self::PLATFORMS)
            ->mapWithKeys(fn ($key) => [$key => in_array($key, $data['platforms'] ?? [], true)])
            ->all();

        $socialLinks = collect($data['social_links'] ?? [])
            ->only(self::SOCIAL_FIELDS)
            ->filter()
            ->all();

        $store->update([
            'description' => $data['description'],
            'location' => $data['location'],
            'platforms' => $platforms,
            'social_links' => $socialLinks,
            'onboarding_submitted_at' => now(),
            ...($request->hasFile('logo')
                ? ['logo' => app(LogoProcessor::class)->process($request->file('logo'))]
                : []),
        ]);

        Mail::to($user->email)->send(new SellerOnboardingReceivedMail($store));

        foreach (User::role('admin')->get() as $admin) {
            Mail::to($admin->email)->send(new SellerOnboardingSubmittedMail($store));

            Notification::make()
                ->title('New seller onboarding submission')
                ->body("{$store->name} has submitted their onboarding details.")
                ->sendToDatabase($admin);
        }

        return back()->with('success', 'Thanks — your onboarding details have been submitted for review.');
    }
}

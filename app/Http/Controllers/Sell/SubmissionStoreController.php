<?php

namespace App\Http\Controllers\Sell;

use App\Http\Controllers\Controller;
use App\Mail\CustomerSellSubmissionAdminAlertMail;
use App\Mail\CustomerSellSubmissionReceivedMail;
use App\Models\CustomerSellSubmission;
use App\Models\User;
use App\Services\PulseApi\PulseApiCardMapper;
use App\Services\PulseApi\PulseApiClient;
use App\Services\Selling\SellOfferCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Filament\Notifications\Notification;

class SubmissionStoreController extends Controller
{
    public function __invoke(Request $request, PulseApiClient $client, SellOfferCalculator $calculator)
    {
        $maxItems = (int) config('selling.max_items_per_submission', 100);

        $validated = $request->validate([
            'customer_name'      => ['required', 'string', 'max:255'],
            'customer_email'     => ['required', 'email', 'max:255'],
            'customer_phone'     => ['nullable', 'string', 'max:50'],
            'customer_postcode'  => ['nullable', 'string', 'max:20'],
            'description'        => ['nullable', 'string', 'max:4000'],
            'items'              => ['required', 'array', 'min:1', "max:{$maxItems}"],
            'items.*.product_id' => ['required', 'string', 'max:255'],
            'items.*.quantity'   => ['required', 'integer', 'min:1', 'max:999'],
        ]);

        // Never trust client-submitted prices — re-resolve every card and recompute
        // the offer server-side, cache-first exactly like Rapid Intake.
        $itemRows = [];
        $errors   = [];

        foreach ($validated['items'] as $index => $item) {
            $productId = $item['product_id'];
            $quantity  = (int) $item['quantity'];

            $attributes = PulseApiCardMapper::cachedAttributes($productId);
            if (! $attributes) {
                $card = $client->getCard($productId);
                $attributes = $card ? PulseApiCardMapper::toInventoryAttributes($card) : null;
            }

            if (! $attributes) {
                $errors["items.{$index}.product_id"] = 'This card could not be found.';
                continue;
            }

            // Defense in depth — the search endpoint already filters these out, but
            // items are submitted by product_id, so a crafted request could otherwise
            // slip a non-Pokémon or non-English card past the frontend entirely.
            if (PulseApiCardMapper::isNonPokemon($attributes['set_id'] ?? null)) {
                $errors["items.{$index}.product_id"] = 'Only Pokémon cards are currently accepted.';
                continue;
            }

            if (! PulseApiCardMapper::isAllowedLanguage($attributes['language'] ?? null)) {
                $errors["items.{$index}.product_id"] = 'Only English-language cards are currently accepted.';
                continue;
            }

            $quote = $calculator->quote($attributes['market_value_pence'], $quantity);
            if (! $quote) {
                $errors["items.{$index}.product_id"] = "{$attributes['card_name']} isn't currently eligible for an automatic offer.";
                continue;
            }

            $itemRows[] = [
                'product_id'         => $attributes['product_id'],
                'card_name'          => $attributes['card_name'],
                'card_number'        => $attributes['card_number'],
                'set_name'           => $attributes['set_name'],
                'rarity'             => $attributes['rarity'],
                'image_url'          => $attributes['image_url'],
                'market_value_pence' => $attributes['market_value_pence'],
                'quantity'           => $quantity,
                'band'               => $quote['band'],
                'offer_percentage'   => $quote['percentage'],
                'unit_offer_pence'   => $quote['unit_offer_pence'],
                'total_offer_pence'  => $quote['total_offer_pence'],
            ];
        }

        if (! empty($errors)) {
            throw ValidationException::withMessages($errors);
        }

        $submission = DB::transaction(function () use ($validated, $itemRows) {
            $submission = CustomerSellSubmission::create([
                'reference'             => CustomerSellSubmission::nextReference(),
                'customer_name'         => $validated['customer_name'],
                'customer_email'        => $validated['customer_email'],
                'customer_phone'        => $validated['customer_phone'] ?? null,
                'customer_postcode'     => $validated['customer_postcode'] ?? null,
                'description'           => $validated['description'] ?? null,
                'status'                => 'submitted',
                'estimated_value_pence' => collect($itemRows)->sum('total_offer_pence'),
            ]);

            $submission->items()->createMany($itemRows);

            return $submission;
        });

        // Acknowledge the customer
        if ($submission->customer_email) {
            Mail::to($submission->customer_email)->send(
                new CustomerSellSubmissionReceivedMail($submission)
            );
        }

        // Notify all admins
        $admins = User::role('admin')->get();

        foreach ($admins as $admin) {
            Mail::to($admin->email)->send(
                new CustomerSellSubmissionAdminAlertMail($submission)
            );

            Notification::make()
                ->title('New customer sell submission')
                ->body("{$submission->customer_name} submitted a new sell request.")
                ->sendToDatabase($admin);
        }

        return redirect()
            ->route('sell.thankyou', ['reference' => $submission->reference]);
    }
}

@php
  /** @var \App\Models\Batch $batch */
  $batch = $getRecord();
  $packs = $batch->packs()->with('card')->orderBy('sequence_no')->get();
  $bands = ['mythic', 'legendary', 'super', 'rare', 'common'];
  $grouped = $packs->groupBy(fn ($pack) => $pack->card?->rarity_band ?? 'unknown');
@endphp

<div class="flex flex-col gap-6 max-h-[32rem] overflow-y-auto pr-1">
  @foreach ($bands as $band)
    @continue(($grouped[$band] ?? collect())->isEmpty())
    <div>
      <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">
        {{ ucfirst($band) }} ({{ $grouped[$band]->count() }})
      </p>
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2">
        @foreach ($grouped[$band]->sortBy('sequence_no') as $pack)
          <div class="flex items-center gap-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 p-2">
            <div class="w-8 h-11 shrink-0 rounded bg-gray-100 dark:bg-black/40 overflow-hidden">
              @if ($pack->card?->image_url)
                <img src="{{ $pack->card->image_url }}" alt="" class="w-full h-full object-cover" loading="lazy">
              @endif
            </div>
            <div class="min-w-0">
              <p class="text-xs font-medium text-gray-900 dark:text-gray-100 truncate">
                {{ $pack->card?->card_name ?? '—' }}
              </p>
              <p class="text-[10px] text-gray-500 dark:text-gray-400 truncate">
                #{{ $pack->sequence_no }} · {{ $pack->status === 'sold' ? 'Sold' : ucfirst($pack->status) }}
              </p>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  @endforeach

  @if (($grouped['unknown'] ?? collect())->isNotEmpty())
    <div>
      <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">
        No card assigned ({{ $grouped['unknown']->count() }})
      </p>
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2">
        @foreach ($grouped['unknown']->sortBy('sequence_no') as $pack)
          <div class="rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 p-2 text-xs text-gray-500 dark:text-gray-400">
            #{{ $pack->sequence_no }} · {{ ucfirst($pack->status) }}
          </div>
        @endforeach
      </div>
    </div>
  @endif
</div>

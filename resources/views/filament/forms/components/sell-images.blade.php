@php
/** @var array<string> $images */
  $images = $getState() ?? [];
  @endphp

  @if (empty($images))
  <p class="text-xs text-gray-500">No images uploaded.</p>
  @else
  <div class="flex flex-wrap gap-3 max-h-64 overflow-auto">
    @foreach ($images as $path)
    <div class="w-24 h-32 rounded overflow-hidden border border-gray-700 bg-black/40">
      <img
        src="{{ Storage::disk('public')->url($path) }}"
        alt=""
        class="w-full h-full object-cover">
    </div>
    @endforeach
  </div>
  @endif
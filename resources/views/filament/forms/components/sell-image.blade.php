<div class="flex flex-wrap gap-2">
  @foreach ($getState() as $path)
  <div class="w-24 h-32 rounded overflow-hidden border border-gray-700 bg-black/40">
    <img src="{{ Storage::disk('public')->url($path) }}" alt="" class="w-full h-full object-cover">
  </div>
  @endforeach
</div>
@if (session()->has('impersonator_id'))
    <div class="w-full bg-[#c9a84c] text-[#0d0b14]">
        <div class="flex items-center justify-center gap-3 px-4 py-2 text-sm font-medium">
            <span>Viewing as {{ auth()->user()?->name }}</span>
            <form method="POST" action="{{ route('impersonate.stop') }}">
                @csrf
                <button type="submit" class="font-semibold uppercase tracking-wide underline underline-offset-2 hover:opacity-70 transition-opacity">
                    Stop impersonating
                </button>
            </form>
        </div>
    </div>
@endif

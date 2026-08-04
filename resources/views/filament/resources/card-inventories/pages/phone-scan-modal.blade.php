<div class="flex flex-col items-center gap-4 py-2">
    <div class="rounded-lg bg-white p-3">
        {!! $svg !!}
    </div>

    <p class="text-xs text-gray-500 dark:text-gray-400 text-center break-all">
        Or open on your phone: <span class="font-mono">{{ $url }}</span>
    </p>

    <p class="text-xs text-gray-500 dark:text-gray-400 text-center">
        Cards scanned on your phone appear in the list below within a few seconds —
        you can leave this window open or close it. The session expires automatically
        after 20 minutes.
    </p>

    <button
        type="button"
        x-on:click="$wire.endPhoneScanSession()"
        class="fi-btn inline-flex items-center gap-1.5 rounded-md px-3 py-2 text-sm font-medium border border-gray-300 dark:border-white/20 text-gray-700 dark:text-gray-200"
    >
        Stop this session
    </button>
</div>

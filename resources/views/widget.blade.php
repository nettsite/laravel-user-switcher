<div class="fixed bottom-4 right-4 z-50 font-sans text-sm" style="max-width: 20rem;">
    @if ($isImpersonating)
        <div class="mb-2 flex items-center justify-between gap-2 rounded-lg border border-amber-300 bg-amber-50 px-3 py-2 text-amber-900 shadow-lg">
            <span>Impersonating</span>
            <button type="button" wire:click="stop" class="rounded bg-amber-600 px-2 py-1 text-white hover:bg-amber-700">
                Stop
            </button>
        </div>
    @endif

    <div class="rounded-lg border border-zinc-300 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-800">
        <button type="button" wire:click="toggle" class="flex w-full items-center justify-between gap-2 px-3 py-2 text-zinc-700 dark:text-zinc-200">
            <span>Switch user</span>
            <span>{{ $open ? '▲' : '▼' }}</span>
        </button>

        @if ($open)
            <div class="max-h-64 overflow-y-auto border-t border-zinc-200 dark:border-zinc-700">
                @forelse ($users as $user)
                    <button
                        type="button"
                        wire:click="switchTo('{{ $user->getKey() }}')"
                        class="block w-full px-3 py-2 text-left hover:bg-zinc-100 dark:hover:bg-zinc-700"
                    >
                        <span class="block font-medium text-zinc-900 dark:text-white">{{ $user->{$labelAttribute} }}</span>
                        <span class="block text-xs text-zinc-500 dark:text-zinc-400">{{ $user->{$identifierAttribute} }}</span>
                    </button>
                @empty
                    <p class="px-3 py-2 text-zinc-500 dark:text-zinc-400">No users found.</p>
                @endforelse
            </div>
        @endif
    </div>
</div>

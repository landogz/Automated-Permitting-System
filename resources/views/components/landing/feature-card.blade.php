@props([
    'title',
    'description',
    'href' => null,
    'label' => 'Explore',
])

<article {{ $attributes->class([
    'landing-reveal group flex h-full flex-col rounded-2xl border border-slate-200/80 bg-white/90 p-5 shadow-sm transition duration-300 sm:p-6',
    'dark:border-slate-800 dark:bg-slate-900/80',
    'hover:-translate-y-0.5 hover:border-emerald-300/60 hover:shadow-lg hover:shadow-emerald-900/5',
    'dark:hover:border-emerald-700/50',
]) }}>
    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-900 text-emerald-300 transition duration-300 group-hover:scale-105 dark:bg-emerald-600 dark:text-white" aria-hidden="true">
        {{ $icon ?? '' }}
    </div>
    <h3 class="mt-4 font-display text-lg font-semibold tracking-tight text-slate-900 dark:text-white">{{ $title }}</h3>
    <p class="mt-2 flex-1 text-sm leading-relaxed text-slate-600 dark:text-slate-400">{{ $description }}</p>
    @if ($href)
        <a href="{{ $href }}" class="mt-4 inline-flex min-h-11 items-center gap-1 text-sm font-semibold text-emerald-800 transition hover:text-emerald-950 dark:text-emerald-400 dark:hover:text-emerald-300">
            {{ __($label) }}
            <span aria-hidden="true">→</span>
        </a>
    @endif
</article>

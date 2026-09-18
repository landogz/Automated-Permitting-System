{{-- GWTD Top Bar — DICT / GWT common look (#222222) --}}
<div class="gwt-topbar" role="navigation" aria-label="{{ __('Government shortcuts') }}">
    <div class="container gwt-topbar__inner">
        <a
            href="https://www.gov.ph"
            class="gwt-topbar__republic"
            rel="noopener noreferrer"
            target="_blank"
            title="{{ __('GOVPH — Official website of the Republic of the Philippines') }}"
        >
            <img
                src="{{ asset('images/branding/republica.png') }}"
                alt=""
                width="28"
                height="28"
                decoding="async"
                aria-hidden="true"
            >
            <span class="gwt-topbar__republic-text">
                <span class="gwt-topbar__govph">GOVPH</span>
                <span class="gwt-topbar__govph-full">{{ __('Republic of the Philippines') }}</span>
            </span>
            <span class="visually-hidden">{{ __('GOVPH — Republic of the Philippines (opens in a new tab)') }}</span>
        </a>

        <ul class="gwt-topbar__nav">
            <li><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
            <li><a href="{{ route('transparency') }}">{{ __('Transparency') }}</a></li>
            <li><a href="{{ route('citizens-charter') }}">{{ __('Citizen’s Charter') }}</a></li>
            <li><a href="{{ route('home') }}#services">{{ __('Services') }}</a></li>
            <li><a href="{{ route('contact') }}">{{ __('Contact Us') }}</a></li>
            <li><a href="{{ route('accessibility') }}">{{ __('Accessibility') }}</a></li>
        </ul>

        <p class="gwt-phst mb-0" aria-live="polite" title="{{ __('Philippine Standard Time (Asia/Manila)') }}">
            <span class="gwt-phst__label">{{ __('Philippine Standard Time') }}</span>
            <time id="gwt-phst-clock" datetime="" class="gwt-phst__time">—</time>
        </p>

        <form class="gwt-search" action="{{ route('sitemap') }}" method="get" role="search">
            <label class="visually-hidden" for="gwt-site-search">{{ __('Search this site') }}</label>
            <input
                type="search"
                id="gwt-site-search"
                name="q"
                class="gwt-search__input"
                placeholder="{{ __('Search…') }}"
                maxlength="120"
                value="{{ request('q') }}"
            >
            <button type="submit" class="gwt-search__btn" aria-label="{{ __('Submit search') }}">
                <i class="ri-search-line" aria-hidden="true"></i>
            </button>
        </form>
    </div>
</div>

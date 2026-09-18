{{-- GWTD Masthead — agency identity --}}
<div class="gwt-masthead">
    <div class="container gwt-masthead__inner">
        <a href="{{ route('home') }}" class="gwt-masthead__brand text-decoration-none">
            <x-branding.logo :height="72" class="gwt-masthead__logo" />
            <div class="gwt-masthead__text">
                <p class="gwt-masthead__republic mb-0">{{ __('Republic of the Philippines') }}</p>
                <p class="gwt-masthead__line mb-0">{{ __('City Government of San Fernando, Pampanga') }}</p>
                <p class="gwt-masthead__agency mb-0">{{ __('Office of the City Building Official') }}</p>
                <p class="gwt-masthead__tagline mb-0">APICS — {{ __('Automated Permitting, Inspection, and Compliance System') }}</p>
            </div>
        </a>
        <div class="gwt-transparency-seal d-none d-md-flex flex-column align-items-center">
            <a href="{{ route('transparency') }}" class="gwt-transparency-seal__link text-decoration-none" title="{{ __('Philippine Transparency Seal') }}">
                <img
                    src="{{ asset('images/branding/philippine-transparency-seal.svg') }}"
                    alt="{{ __('Philippine Transparency Seal — view mandated disclosures') }}"
                    width="88"
                    height="88"
                    decoding="async"
                >
            </a>
            <a
                href="https://www.foi.gov.ph"
                class="gwt-transparency-seal__foi"
                rel="noopener noreferrer"
                target="_blank"
                title="{{ __('Freedom of Information — foi.gov.ph') }}"
            >{{ __('FOI') }}</a>
        </div>
    </div>
</div>

{{--
  GWTD Standard Footer (DICT)
  Required: Republic Seal, public-domain line directly under seal,
  Government Directory, User Policies (IPR, Data Privacy, Security)
--}}
<footer class="gwt-standard-footer" id="standard-footer" aria-label="{{ __('Government standard footer') }}">
    <div class="container">
        <div class="gwt-standard-footer__seal-wrap">
            <img
                src="{{ asset('images/branding/republica.png') }}"
                alt="{{ __('Seal of the Republic of the Philippines') }}"
                class="gwt-standard-footer__seal-img"
                width="140"
                height="140"
                decoding="async"
            >
            <p class="gwt-standard-footer__nation mb-0">{{ __('Republic of the Philippines') }}</p>
            <p class="gwt-standard-footer__public-domain mb-0">
                {{ __('All content is public domain unless otherwise stated.') }}
            </p>
        </div>

        <nav class="gwt-standard-footer__directory" aria-label="{{ __('Government directory') }}">
            <a href="https://www.gov.ph" rel="noopener noreferrer" target="_blank">{{ __('GOVPH') }}</a>
            <span class="gwt-standard-footer__sep" aria-hidden="true">|</span>
            <a href="https://www.gov.ph/directory/" rel="noopener noreferrer" target="_blank">{{ __('Government Directory') }}</a>
            <span class="gwt-standard-footer__sep" aria-hidden="true">|</span>
            <a href="https://data.gov.ph" rel="noopener noreferrer" target="_blank">{{ __('Open Data Portal') }}</a>
            <span class="gwt-standard-footer__sep" aria-hidden="true">|</span>
            <a href="https://www.foi.gov.ph" rel="noopener noreferrer" target="_blank">{{ __('Freedom of Information') }}</a>
        </nav>

        <nav class="gwt-standard-footer__policies" aria-label="{{ __('User policies') }}">
            <a href="{{ route('intellectual-property') }}">{{ __('Intellectual Property Rights Policy') }}</a>
            <span class="gwt-standard-footer__sep" aria-hidden="true">|</span>
            <a href="{{ route('privacy') }}">{{ __('Data Privacy Policy') }}</a>
            <span class="gwt-standard-footer__sep" aria-hidden="true">|</span>
            <a href="{{ route('security-policy') }}">{{ __('Security Policy') }}</a>
        </nav>

        <p class="gwt-standard-footer__credit mb-0">
            &copy; <span id="gwt-year"></span>
            {{ config('apics_public.agency_name') }} ·
            {{ config('apics_public.lgu_name') }} · APICS
        </p>
    </div>
</footer>

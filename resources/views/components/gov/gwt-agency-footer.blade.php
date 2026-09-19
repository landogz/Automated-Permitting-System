{{--
  GWTD Agency Footer (DICT Government Website Template Design)
  Required across all pages: Downloads, Archives, Site Map, FAQs
--}}
<footer class="gwt-agency-footer" id="agency-footer" aria-label="{{ __('Agency footer') }}">
    <div class="container">
        <div class="row g-4 gwt-agency-footer__grid">
            <div class="col-12 col-lg-4">
                <div class="gwt-agency-footer__brand">
                    <x-branding.logo :height="48" class="gwt-agency-footer__logo" />
                    <div>
                        <p class="gwt-agency-footer__republic mb-0">{{ __('Republic of the Philippines') }}</p>
                        <h2 class="gwt-agency-footer__title">{{ config('apics_public.agency_name') }}</h2>
                        <p class="gwt-agency-footer__tagline mb-0">APICS · {{ config('apics_public.lgu_name') }}</p>
                    </div>
                </div>
                <p class="gwt-agency-footer__address mb-0">
                    <a href="{{ route('contact') }}">{{ __('Contact Us') }}</a>
                    <span aria-hidden="true"> · </span>
                    <a href="{{ route('accessibility') }}">{{ __('Accessibility') }}</a>
                </p>
            </div>

            <div class="col-6 col-md-3 col-lg-2">
                <h3 class="gwt-agency-footer__heading">{{ __('Downloads') }}</h3>
                <ul class="gwt-agency-footer__list">
                    <li><a href="{{ route('downloads') }}">{{ __('Forms & manuals') }}</a></li>
                    <li><a href="{{ route('citizens-charter') }}">{{ __('Citizen’s Charter') }}</a></li>
                    <li><a href="{{ route('transparency') }}">{{ __('Transparency docs') }}</a></li>
                </ul>
            </div>

            <div class="col-6 col-md-3 col-lg-2">
                <h3 class="gwt-agency-footer__heading">{{ __('Archives') }}</h3>
                <ul class="gwt-agency-footer__list">
                    <li><a href="{{ route('archives') }}">{{ __('News & releases') }}</a></li>
                    <li><a href="{{ route('archives') }}#2026">{{ __('2026') }}</a></li>
                    <li><a href="{{ route('faqs') }}">{{ __('FAQs') }}</a></li>
                </ul>
            </div>

            <div class="col-6 col-md-3 col-lg-2">
                <h3 class="gwt-agency-footer__heading">{{ __('Site map') }}</h3>
                <ul class="gwt-agency-footer__list">
                    <li><a href="{{ route('sitemap') }}">{{ __('Full sitemap') }}</a></li>
                    <li><a href="{{ route('documentation.privileges', ['locale' => 'en']) }}">{{ __('Privileges (EN)') }}</a></li>
                    <li><a href="{{ route('documentation.privileges', ['locale' => 'tl']) }}">{{ __('Pribilehiyo (TL)') }}</a></li>
                </ul>
            </div>

            <div class="col-6 col-md-3 col-lg-2">
                <h3 class="gwt-agency-footer__heading">{{ __('Open government') }}</h3>
                <ul class="gwt-agency-footer__list">
                    <li><a href="{{ route('transparency') }}">{{ __('Transparency Seal') }}</a></li>
                    <li><a href="https://www.foi.gov.ph" rel="noopener noreferrer" target="_blank">{{ __('Freedom of Information') }}</a></li>
                    <li><a href="{{ route('privacy') }}">{{ __('Privacy Notice') }}</a></li>
                </ul>
            </div>
        </div>
    </div>
</footer>

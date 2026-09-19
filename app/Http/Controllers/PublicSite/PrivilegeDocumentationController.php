<?php

declare(strict_types=1);

namespace App\Http\Controllers\PublicSite;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Public bilingual privilege / role documentation.
 */
class PrivilegeDocumentationController
{
    /**
     * Show privilege documentation in English or Tagalog.
     */
    public function __invoke(Request $request, string $locale = 'en'): View|RedirectResponse
    {
        $locale = strtolower($locale);
        if (! in_array($locale, ['en', 'tl'], true)) {
            return redirect()->route('documentation.privileges', ['locale' => 'en']);
        }

        $catalog = config('apics_privileges');
        $intro = $catalog['intro'][$locale] ?? $catalog['intro']['en'];

        return view('public.documentation.privileges', [
            'locale' => $locale,
            'intro' => $intro,
            'applicant' => $catalog['applicant'][$locale] ?? $catalog['applicant']['en'],
            'privileges' => $catalog['privileges'] ?? [],
            'roles' => $catalog['roles'] ?? [],
            'flowchart' => $catalog['flowchart'] ?? [],
            'title' => $intro['title'],
            'altLocale' => $locale === 'en' ? 'tl' : 'en',
            'altLocaleLabel' => $locale === 'en' ? 'Tagalog' : 'English',
        ]);
    }
}

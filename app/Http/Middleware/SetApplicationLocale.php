<?php

namespace App\Http\Middleware;

use App\Models\Installation;
use App\Models\User;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Number;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Choose the language of the request, first match wins:
 * ?lang= → session → the user's choice → the campus default →
 * the installation default → the browser's Accept-Language → APP_DEFAULT_LOCALE.
 */
class SetApplicationLocale
{
    /**
     * The session key holding the language chosen in this browser.
     */
    public const SESSION_KEY = 'locale';

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolve($request);

        app()->setLocale($locale);
        Carbon::setLocale($locale);
        Number::useLocale((string) config("app.number_locales.$locale", $locale));

        return $next($request);
    }

    /**
     * Pick the first supported language from the sources, in priority order.
     */
    public function resolve(Request $request): string
    {
        $fromQuery = $this->supported($request->query('lang'));

        if ($fromQuery !== null) {
            if ($request->hasSession()) {
                $request->session()->put(self::SESSION_KEY, $fromQuery);
            }

            return $fromQuery;
        }

        $user = $request->user();

        return $this->supported($request->hasSession() ? $request->session()->get(self::SESSION_KEY) : null)
            ?? $this->supported($user instanceof User ? $user->locale : null)
            ?? $this->supported(current_school()?->default_locale)
            ?? $this->supported($this->installationLocale())
            ?? $this->browserLocale($request)
            ?? $this->supported(config('app.locale'))
            ?? (string) config('app.fallback_locale');
    }

    /**
     * Return the language when Campuly speaks it, else null.
     */
    private function supported(mixed $locale): ?string
    {
        if (!is_string($locale) || $locale === '') {
            return null;
        }

        $locale = strtolower(substr(str_replace('-', '_', $locale), 0, 2));

        return in_array($locale, $this->supportedLocales(), true) ? $locale : null;
    }

    /**
     * @return list<string>
     */
    private function supportedLocales(): array
    {
        return array_keys((array) config('app.supported_locales', []));
    }

    /**
     * The first language in the browser's Accept-Language list that Campuly speaks.
     *
     * Not getPreferredLanguage(): it answers the first supported language even
     * when the browser asked for none of them.
     */
    private function browserLocale(Request $request): ?string
    {
        foreach ($request->getLanguages() as $language) {
            $supported = $this->supported($language);

            if ($supported !== null) {
                return $supported;
            }
        }

        return null;
    }

    private function installationLocale(): ?string
    {
        try {
            $locale = Installation::withoutGlobalScopes()->value('locale');
        } catch (Throwable) {
            // The installer must still render before the application schema exists.
            return null;
        }

        return is_string($locale) ? $locale : null;
    }
}

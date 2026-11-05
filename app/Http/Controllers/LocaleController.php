<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetApplicationLocale;
use App\Http\Requests\UpdateLocaleRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cookie;

/**
 * Switch the language, keep the person on the same page and signed in.
 */
class LocaleController extends Controller
{
    /**
     * How long the browser remembers the choice, in minutes (one year).
     */
    public const COOKIE_MINUTES = 60 * 24 * 365;

    public function __invoke(UpdateLocaleRequest $request): RedirectResponse
    {
        $locale = (string) $request->validated('locale');
        $user = $request->user();

        if ($user instanceof User && $user->locale !== $locale) {
            $user->forceFill(['locale' => $locale])->save();
        }

        $request->session()->put(SetApplicationLocale::SESSION_KEY, $locale);
        Cookie::queue(SetApplicationLocale::COOKIE_NAME, $locale, self::COOKIE_MINUTES);

        return redirect()->back(fallback: route('dashboard'));
    }
}

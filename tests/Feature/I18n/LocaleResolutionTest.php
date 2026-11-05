<?php

namespace Tests\Feature\I18n;

use App\Actions\Installation\CompleteExistingInstallation;
use App\Http\Middleware\SetApplicationLocale;
use App\Models\Installation;
use App\Models\School;
use App\Models\User;
use App\Traits\FeatureTestTrait;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Number;
use Tests\TestCase;

/**
 * The language of a request, first match wins:
 * ?lang= → session → user → campus → installation → Accept-Language → APP_DEFAULT_LOCALE.
 */
class LocaleResolutionTest extends TestCase
{
    use FeatureTestTrait;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(CompleteExistingInstallation::class)->recordIfReady();
    }

    public function test_the_installation_default_applies_when_nothing_closer_decides(): void
    {
        $this->installationSpeaks('fr');

        $this->get(route('login'))->assertOk()->assertSee('<html lang="fr"', false);
    }

    public function test_the_browser_language_applies_when_the_installation_has_none(): void
    {
        $this->installationSpeaks('xx');

        $this->withHeader('Accept-Language', 'de-DE,fr-CM;q=0.8,en;q=0.5')
            ->get(route('login'))
            ->assertSee('<html lang="fr"', false);
    }

    public function test_the_configured_default_applies_when_the_browser_asks_for_nothing_campuly_speaks(): void
    {
        $this->installationSpeaks('xx');
        config(['app.locale' => 'fr']);

        $this->withHeader('Accept-Language', 'de-DE')->get(route('login'))->assertSee('<html lang="fr"', false);
    }

    public function test_the_campus_default_beats_the_installation(): void
    {
        Installation::withoutGlobalScopes()->update(['locale' => 'en']);
        $this->campusSpeaks('fr');
        $user = $this->signedIn(locale: null);

        $user->get(route('dashboard'))->assertOk()->assertSee('<html lang="fr"', false);
    }

    public function test_the_person_s_own_choice_beats_the_campus(): void
    {
        $this->campusSpeaks('fr');
        $user = $this->signedIn(locale: 'en');

        $user->get(route('dashboard'))->assertOk()->assertSee('<html lang="en"', false);
    }

    public function test_the_session_beats_the_person_s_saved_choice(): void
    {
        $user = $this->signedIn(locale: 'en');

        $user->withSession([SetApplicationLocale::SESSION_KEY => 'fr'])
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('<html lang="fr"', false);
    }

    public function test_the_query_string_beats_everything_and_is_remembered(): void
    {
        $user = $this->signedIn(locale: 'en');

        $user->withSession([SetApplicationLocale::SESSION_KEY => 'en'])
            ->get(route('dashboard', ['lang' => 'fr']))
            ->assertOk()
            ->assertSee('<html lang="fr"', false)
            ->assertSessionHas(SetApplicationLocale::SESSION_KEY, 'fr');
    }

    public function test_an_unknown_language_is_ignored(): void
    {
        $this->installationSpeaks('en');

        $this->get(route('login', ['lang' => 'de']))
            ->assertSee('<html lang="en"', false)
            ->assertSessionMissing(SetApplicationLocale::SESSION_KEY);
    }

    public function test_dates_and_numbers_follow_the_language(): void
    {
        $this->installationSpeaks('fr');

        $this->get(route('login'))->assertOk();

        $this->assertSame('fr', app()->getLocale());
        $this->assertSame('fr', Carbon::getLocale());
        $this->assertSame('fr_CM', Number::defaultLocale());
    }

    public function test_mail_uses_the_recipient_s_language(): void
    {
        $this->campusSpeaks('fr');

        $this->assertSame('en', User::factory()->make(['locale' => 'en'])->preferredLocale());
        $this->assertSame('fr', User::factory()->make(['locale' => null])->preferredLocale());
    }

    public function test_a_new_campus_takes_the_installation_s_default_language(): void
    {
        config(['app.locale' => 'fr']);

        $this->assertSame('fr', School::factory()->create()->default_locale);
    }

    /**
     * Set the installation default, for a visitor who is in no campus yet.
     */
    private function installationSpeaks(string $locale): void
    {
        Installation::withoutGlobalScopes()->update(['locale' => $locale]);
        school_context()->set(null, remember: false);
    }

    private function campusSpeaks(string $locale): void
    {
        $school = $this->workingSchool();
        $school->update(['default_locale' => $locale]);
        school_context()->set($school->fresh(), remember: false);
    }

    /**
     * Sign in a member of the working school with the given saved language.
     */
    private function signedIn(?string $locale): static
    {
        $actor = $this->authorized_user([]);
        User::query()->whereKey(auth()->id())->update(['locale' => $locale]);
        auth()->user()?->refresh();

        return $actor;
    }
}

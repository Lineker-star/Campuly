<?php

namespace Tests\Feature\I18n;

use App\Actions\Installation\CompleteExistingInstallation;
use App\Http\Middleware\SetApplicationLocale;
use App\Livewire\Layouts\Menu;
use App\Models\Installation;
use App\Models\User;
use App\Traits\FeatureTestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Switching language keeps the person on the same page and signed in.
 */
class LanguageSwitcherTest extends TestCase
{
    use FeatureTestTrait;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(CompleteExistingInstallation::class)->recordIfReady();
        Installation::withoutGlobalScopes()->update(['locale' => 'en']);
    }

    public function test_a_guest_switches_on_the_sign_in_page_and_stays_there(): void
    {
        school_context()->set(null, remember: false);

        $this->from(route('login'))
            ->post(route('locale.update'), ['locale' => 'fr'])
            ->assertRedirect(route('login'))
            ->assertSessionHas(SetApplicationLocale::SESSION_KEY, 'fr')
            ->assertCookie(SetApplicationLocale::COOKIE_NAME, 'fr');

        $this->get(route('login'))->assertSee('<html lang="fr"', false);
    }

    public function test_a_guest_s_choice_survives_the_end_of_the_session(): void
    {
        school_context()->set(null, remember: false);

        $this->withCookie(SetApplicationLocale::COOKIE_NAME, 'fr')
            ->get(route('login'))
            ->assertSee('<html lang="fr"', false);
    }

    public function test_a_signed_in_person_switches_keeps_the_page_and_stays_signed_in(): void
    {
        $actor = $this->authorized_user([]);
        $user = auth()->user();

        $actor->from(route('dashboard'))
            ->post(route('locale.update'), ['locale' => 'fr'])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertSame('fr', $user->fresh()->locale);
        $actor->get(route('dashboard'))->assertOk()->assertSee('<html lang="fr"', false);
    }

    public function test_a_language_campuly_does_not_speak_is_refused(): void
    {
        $actor = $this->authorized_user([]);

        $actor->from(route('dashboard'))
            ->post(route('locale.update'), ['locale' => 'de'])
            ->assertSessionHasErrors('locale');

        $this->assertNull(User::query()->find(auth()->id())?->locale);
    }

    public function test_the_sign_in_page_offers_both_languages_and_marks_the_current_one(): void
    {
        school_context()->set(null, remember: false);

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('action="'.route('locale.update').'"', false)
            ->assertSee('value="en" lang="en"', false)
            ->assertSee('aria-pressed="true"', false)
            ->assertSee('value="fr" lang="fr"', false)
            ->assertSee('aria-pressed="false"', false);
    }

    public function test_the_header_switcher_answers_to_alt_l_and_the_palette(): void
    {
        $html = (string) $this->authorized_user([])->get(route('dashboard'))->assertOk()->getContent();

        $this->assertStringContainsString("\$event.code === 'KeyL'", $html);
        $this->assertStringContainsString('x-on:switch-language.window', $html);
    }

    public function test_the_command_palette_offers_to_switch_language(): void
    {
        $this->authorized_user([]);

        $items = Livewire::test(Menu::class)->get('commandItems');
        $item = collect($items)->firstWhere('key', 'switch-language');

        $this->assertNotNull($item);
        $this->assertSame('switch-language', $item['action']);
        $this->assertSame('Switch language / Changer de langue', $item['label']);
    }

    public function test_an_error_page_switches_with_links_that_need_no_session(): void
    {
        $this->get('/a-page-that-does-not-exist')
            ->assertNotFound()
            ->assertSee('lang=fr', false)
            ->assertSee('hreflang="fr"', false);
    }
}

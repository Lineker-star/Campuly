<?php

namespace Tests\Feature\I18n;

use App\Http\Middleware\SetApplicationLocale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Sign-in, password reset, error pages and validation speak French.
 */
class AuthTranslationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        school_context()->set(null, remember: false);
    }

    public function test_the_sign_in_page_is_french(): void
    {
        $this->inFrench()->get(route('login'))
            ->assertOk()
            ->assertSee('Bon retour parmi nous')
            ->assertSee('Mot de passe oublié', false)
            ->assertDontSee('Welcome back')
            ->assertDontSee('Forgot your password?');
    }

    public function test_a_failed_sign_in_and_validation_messages_are_french(): void
    {
        $user = User::factory()->create();

        $this->inFrench()->from(route('login'))
            ->post(route('login'), ['email' => $user->email, 'password' => 'not-the-password'])
            ->assertSessionHasErrors(['email' => 'Ces identifiants ne correspondent à aucun compte.']);

        $this->inFrench()->from(route('login'))
            ->post(route('login'), ['email' => '', 'password' => ''])
            ->assertSessionHasErrors(['email' => 'Le champ adresse e-mail est obligatoire.']);
    }

    public function test_the_password_reset_request_page_is_french(): void
    {
        $this->inFrench()->get(route('password.request'))
            ->assertOk()
            ->assertSee('Nous vous enverrons un lien de réinitialisation par e-mail.', false);
    }

    public function test_an_error_page_is_french(): void
    {
        $this->get('/a-page-that-does-not-exist?lang=fr')
            ->assertNotFound()
            ->assertSee('Page introuvable')
            ->assertDontSee('Page not found');
    }

    private function inFrench(): static
    {
        return $this->withSession([SetApplicationLocale::SESSION_KEY => 'fr']);
    }
}

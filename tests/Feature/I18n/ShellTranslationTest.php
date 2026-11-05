<?php

namespace Tests\Feature\I18n;

use App\Http\Middleware\SetApplicationLocale;
use App\Traits\FeatureTestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The frame around every screen (sidebar, header, command palette) speaks French.
 */
class ShellTranslationTest extends TestCase
{
    use FeatureTestTrait;
    use RefreshDatabase;

    public function test_the_sidebar_header_and_palette_are_french(): void
    {
        $html = $this->frenchDashboard();

        foreach (['Tableau de bord', 'Espace de travail', 'Élèves', 'Enseignants', 'Emplois du temps', 'Se déconnecter',
            'Rechercher des pages et fonctions', 'Aucune page ni fonction trouvée.', 'Plein écran', 'Choisir le thème',
            'Documentation (s’ouvre dans un nouvel onglet)'] as $french) {
            $this->assertStringContainsString(e($french), $html, "Missing French text: $french");
        }
    }

    public function test_no_english_shell_label_is_left_in_french(): void
    {
        $html = $this->frenchDashboard();

        foreach (['>Log out<', '<span>Students</span>', '<span>Teachers</span>', '<span>Dashboard</span>', 'Full screen mode', 'aria-label="Main"',
            'aria-label="Working school"', 'Search pages and features', 'No pages or features found.'] as $english) {
            $this->assertStringNotContainsString($english, $html, "English left in French mode: $english");
        }
    }

    public function test_the_school_s_own_vocabulary_passes_through_unchanged(): void
    {
        $this->assertSame('Form 1 students', __('Form 1 students'));
    }

    private function frenchDashboard(): string
    {
        $actor = $this->platform_admin();

        return (string) $actor->withSession([SetApplicationLocale::SESSION_KEY => 'fr'])
            ->get(route('dashboard'))
            ->assertOk()
            ->getContent();
    }
}

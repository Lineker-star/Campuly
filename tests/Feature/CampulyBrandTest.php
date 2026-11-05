<?php

namespace Tests\Feature;

use App\Traits\FeatureTestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Campuly keeps the upstream skuul: command names working, but shows its own
 * name and links everywhere a person can see them.
 */
class CampulyBrandTest extends TestCase
{
    use FeatureTestTrait;
    use RefreshDatabase;

    public function test_every_skuul_command_also_answers_to_its_campuly_name(): void
    {
        $commands = Artisan::all();
        $upstream = array_filter(array_keys($commands), fn (string $name): bool => str_starts_with($name, 'skuul:'));

        $this->assertNotEmpty($upstream);

        foreach ($upstream as $name) {
            $alias = 'campuly:'.Str::after($name, 'skuul:');

            $this->assertArrayHasKey($alias, $commands, "{$name} has no {$alias} alias.");
            $this->assertSame($commands[$name], $commands[$alias], "{$alias} must run the same command as {$name}.");
        }
    }

    public function test_the_campuly_name_runs_the_command(): void
    {
        $this->artisan('campuly:prune-expired-invitations')->assertSuccessful();
    }

    public function test_the_documentation_links_follow_the_configured_address(): void
    {
        config(['campuly.docs_url' => 'https://help.example.test/campuly']);

        $html = (string) $this->authorized_user([])->get(route('dashboard'))->assertOk()->getContent();

        $this->assertSame(3, substr_count($html, 'href="https://help.example.test/campuly"'));
    }

    public function test_the_dashboard_and_sign_in_screens_do_not_show_the_upstream_name(): void
    {
        $signIn = (string) $this->get(route('login'))->assertOk()->getContent();
        $this->assertStringNotContainsStringIgnoringCase('skuul', $signIn);

        $dashboard = (string) $this->authorized_user([])->get(route('dashboard'))->assertOk()->getContent();
        $this->assertStringNotContainsStringIgnoringCase('skuul', $dashboard);
    }

    public function test_updates_come_from_the_campuly_fork_unless_the_install_says_otherwise(): void
    {
        $this->assertSame('Lineker-star/Campuly', $this->releaseConfigWith([]));
        $this->assertSame('acme/campuly', $this->releaseConfigWith(['CAMPULY_UPDATE_REPOSITORY' => 'acme/campuly']));
        $this->assertSame('legacy/skuul', $this->releaseConfigWith(['SKUUL_UPDATE_REPOSITORY' => 'legacy/skuul']));
    }

    /**
     * Read config/release.php with only the given update variables set.
     *
     * @param  array<string, string>  $environment
     */
    private function releaseConfigWith(array $environment): string
    {
        $names = ['CAMPULY_UPDATE_REPOSITORY', 'SKUUL_UPDATE_REPOSITORY'];
        $saved = [];

        foreach ($names as $name) {
            $saved[$name] = [$_ENV[$name] ?? null, $_SERVER[$name] ?? null, getenv($name)];
            unset($_ENV[$name], $_SERVER[$name]);
            putenv($name);
        }

        foreach ($environment as $name => $value) {
            $_ENV[$name] = $_SERVER[$name] = $value;
            putenv("{$name}={$value}");
        }

        try {
            return (require config_path('release.php'))['update_repository'];
        } finally {
            foreach ($saved as $name => [$env, $server, $process]) {
                unset($_ENV[$name], $_SERVER[$name]);
                putenv($name);

                if ($env !== null) {
                    $_ENV[$name] = $env;
                }

                if ($server !== null) {
                    $_SERVER[$name] = $server;
                }

                if ($process !== false) {
                    putenv("{$name}={$process}");
                }
            }
        }
    }
}

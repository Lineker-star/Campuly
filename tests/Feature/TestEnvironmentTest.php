<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The suite resets its database, so it must only ever reach the throwaway
 * testing database of the engine it runs on (see docs/DATABASE.md).
 */
class TestEnvironmentTest extends TestCase
{
    public function test_suite_uses_the_testing_database(): void
    {
        $connection = config('database.default');

        $this->assertTrue(app()->environment('testing'));
        $this->assertContains($connection, ['mysql', 'pgsql', 'sqlite']);
        $this->assertEmpty(config("database.connections.{$connection}.url"));

        match ($connection) {
            'mysql' => $this->assertSame('testing', DB::scalar('select database()')),
            'pgsql' => $this->assertSame('testing', DB::scalar('select current_database()')),
            'sqlite' => $this->assertStringEndsWith('testing.sqlite', str_replace('\\', '/', DB::connection()->getDatabaseName())),
        };

        if ($connection !== 'sqlite') {
            $this->assertSame('testing', DB::connection()->getDatabaseName());
        }
    }
}

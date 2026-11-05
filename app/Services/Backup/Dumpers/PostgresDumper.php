<?php

namespace App\Services\Backup\Dumpers;

use App\Contracts\DatabaseDumper;
use App\Exceptions\InvalidValueException;
use Symfony\Component\Process\Process;

/**
 * Dump and restore PostgreSQL (including Supabase and Neon) with pg_dump and
 * psql. Credentials travel in PG* environment variables, never in the
 * process arguments other users can see.
 */
class PostgresDumper implements DatabaseDumper
{
    /**
     * How long a dump or a restore may run, in seconds.
     */
    private const TIMEOUT = 3600;

    /**
     * Get the connection driver this dumper handles.
     */
    public function driver(): string
    {
        return 'pgsql';
    }

    /**
     * Check whether the tools this dumper needs are installed.
     */
    public function isAvailable(): bool
    {
        return $this->found('pg_dump') && $this->found('psql');
    }

    /**
     * Write the whole database to one file.
     *
     * pg_dump reads one consistent snapshot. Ownership and grants are left
     * out, so the file restores into a database owned by another role, as
     * hosted PostgreSQL requires.
     *
     * @param  array<string, mixed>  $connection
     */
    public function dumpTo(string $path, array $connection): void
    {
        $this->run('pg_dump', $connection, [
            '--format=plain',
            '--no-owner',
            '--no-privileges',
            '--file='.$path,
        ]);
    }

    /**
     * Load one file back into a database.
     *
     * @param  array<string, mixed>  $connection
     */
    public function restoreFrom(string $path, array $connection): void
    {
        $this->run('psql', $connection, [
            '--quiet',
            '--no-psqlrc',
            '--set=ON_ERROR_STOP=1',
            '--file='.$path,
        ]);
    }

    /**
     * Run one of the PostgreSQL tools against the connection.
     *
     * @param  array<string, mixed>  $connection
     * @param  array<int, string>  $arguments
     */
    private function run(string $tool, array $connection, array $arguments): void
    {
        $process = new Process([$tool, ...$arguments], null, $this->environment($connection));
        $process->setTimeout(self::TIMEOUT);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new InvalidValueException(__(':tool failed: :get_error_output', ['tool' => $tool, 'get_error_output' => trim($process->getErrorOutput())]));
        }
    }

    /**
     * The libpq variables that name the server, database and credentials.
     *
     * @param  array<string, mixed>  $connection
     * @return array<string, string>
     */
    private function environment(array $connection): array
    {
        return array_filter([
            'PGHOST' => (string) ($connection['host'] ?? '127.0.0.1'),
            'PGPORT' => (string) ($connection['port'] ?? 5432),
            'PGDATABASE' => (string) ($connection['database'] ?? ''),
            'PGUSER' => (string) ($connection['username'] ?? ''),
            'PGPASSWORD' => (string) ($connection['password'] ?? ''),
            'PGSSLMODE' => (string) ($connection['sslmode'] ?? ''),
        ], fn (string $value): bool => $value !== '');
    }

    /**
     * Check whether one tool is installed.
     */
    private function found(string $tool): bool
    {
        $process = new Process([$tool, '--version']);
        $process->run();

        return $process->isSuccessful();
    }
}

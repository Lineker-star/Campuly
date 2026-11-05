<?php

namespace App\Services\Backup;

use App\Contracts\DatabaseDumper;
use App\Exceptions\InvalidValueException;
use App\Services\Backup\Dumpers\MysqlDumper;
use App\Services\Backup\Dumpers\PostgresDumper;
use App\Services\Backup\Dumpers\SqliteDumper;
use Illuminate\Support\Facades\DB;

/**
 * Resolve registered dumpers by connection driver for backup and restore commands.
 */
class DatabaseDumperRegistry
{
    /**
     * The dumpers, by the driver they handle.
     *
     * @var array<int, class-string<DatabaseDumper>>
     */
    private const DUMPERS = [
        MysqlDumper::class,
        PostgresDumper::class,
        SqliteDumper::class,
    ];

    /**
     * Get the dumper of one connection.
     *
     * @throws InvalidValueException when the engine has no dumper, or its tools are missing
     */
    public function forConnection(string $connection): DatabaseDumper
    {
        $driver = (string) DB::connection($connection)->getConfig('driver');
        $dumper = $this->forDriver($driver);

        if (!$dumper->isAvailable()) {
            throw new InvalidValueException(__('The :driver tools are not installed on this machine, so no backup can be taken.', ['driver' => $driver]));
        }

        return $dumper;
    }

    /**
     * Get the dumper of one engine, installed or not.
     *
     * @throws InvalidValueException when no dumper handles the engine
     */
    public function forDriver(string $driver): DatabaseDumper
    {
        foreach (self::DUMPERS as $class) {
            /** @var DatabaseDumper $dumper */
            $dumper = app($class);

            if ($dumper->driver() === $driver) {
                return $dumper;
            }
        }

        throw new InvalidValueException(__('This application cannot back up a :driver database yet.', ['driver' => $driver]));
    }
}

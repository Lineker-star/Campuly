<?php

namespace App\Services\Backup\Dumpers;

use App\Contracts\DatabaseDumper;
use App\Exceptions\InvalidValueException;
use PDO;

/**
 * Dump a SQLite database to SQL text, like mysqldump and pg_dump do, so the
 * backup and the restore rehearsal treat every engine the same way. Runs in
 * PHP, so the campus node needs no extra tools.
 */
class SqliteDumper implements DatabaseDumper
{
    /**
     * Get the connection driver this dumper handles.
     */
    public function driver(): string
    {
        return 'sqlite';
    }

    /**
     * Check whether the tools this dumper needs are installed.
     */
    public function isAvailable(): bool
    {
        return extension_loaded('pdo_sqlite');
    }

    /**
     * Write the whole database to one file.
     *
     * The dump is read inside one transaction, so it is the database as it
     * stood at one moment.
     *
     * @param  array<string, mixed>  $connection
     */
    public function dumpTo(string $path, array $connection): void
    {
        $pdo = $this->open($this->databaseFile($connection));
        $out = fopen($path, 'wb');

        if ($out === false) {
            throw new InvalidValueException(__('The file [:path] could not be written.', ['path' => $path]));
        }

        $pdo->beginTransaction();

        try {
            fwrite($out, "PRAGMA foreign_keys=OFF;\nBEGIN TRANSACTION;\n");

            $objects = $pdo->query(
                "select type, name, sql from sqlite_master where sql is not null and name not like 'sqlite_%' "
                ."order by case type when 'table' then 0 when 'index' then 1 when 'view' then 2 else 3 end, name"
            )->fetchAll(PDO::FETCH_ASSOC);

            foreach ($objects as $object) {
                fwrite($out, $object['sql'].";\n");

                if ($object['type'] === 'table') {
                    $this->writeRows($pdo, $out, $object['name']);
                }
            }

            fwrite($out, "COMMIT;\n");
        } finally {
            $pdo->commit();
            fclose($out);
        }
    }

    /**
     * Load one file back into a database.
     *
     * @param  array<string, mixed>  $connection
     */
    public function restoreFrom(string $path, array $connection): void
    {
        $database = $this->databaseFile($connection, mustExist: false);

        if (!is_file($database)) {
            touch($database);
        }

        $sql = file_get_contents($path);

        if ($sql === false) {
            throw new InvalidValueException(__('The file [:path] could not be read.', ['path' => $path]));
        }

        $this->open($database)->exec($sql);
    }

    /**
     * Write one table's rows as INSERT statements.
     *
     * @param  resource  $out
     */
    private function writeRows(PDO $pdo, $out, string $table): void
    {
        $quotedTable = '"'.str_replace('"', '""', $table).'"';
        $rows = $pdo->query("select * from $quotedTable");

        while (($row = $rows->fetch(PDO::FETCH_NUM)) !== false) {
            $values = array_map(fn ($value): string => match (true) {
                $value === null => 'NULL',
                is_int($value), is_float($value) => (string) $value,
                default => $pdo->quote((string) $value),
            }, $row);

            fwrite($out, "INSERT INTO $quotedTable VALUES(".implode(',', $values).");\n");
        }
    }

    private function open(string $database): PDO
    {
        return new PDO('sqlite:'.$database, options: [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_STRINGIFY_FETCHES => false,
        ]);
    }

    /**
     * Get the database file of the connection.
     *
     * @param  array<string, mixed>  $connection
     */
    private function databaseFile(array $connection, bool $mustExist = true): string
    {
        $database = (string) ($connection['database'] ?? '');

        if ($database === '' || $database === ':memory:') {
            throw new InvalidValueException(__('An in-memory SQLite database has no file to back up or restore into.'));
        }

        if ($mustExist && !is_file($database)) {
            throw new InvalidValueException(__('The SQLite database file [:database] does not exist.', ['database' => $database]));
        }

        return $database;
    }
}

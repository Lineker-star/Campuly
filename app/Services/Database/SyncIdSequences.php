<?php

namespace App\Services\Database;

use Illuminate\Support\Facades\DB;

/**
 * Move every PostgreSQL id sequence past the highest id already stored.
 *
 * Seeders write rows with fixed ids (the first school, the first admin).
 * MySQL and SQLite then carry on from the highest id by themselves; a
 * PostgreSQL sequence does not move, so the next ordinary insert would try
 * id 1 again and fail.
 */
class SyncIdSequences
{
    /**
     * Run the sync on the given connection, and say how many sequences moved.
     */
    public function handle(?string $connection = null): int
    {
        $database = DB::connection($connection);

        if ($database->getDriverName() !== 'pgsql') {
            return 0;
        }

        $sequences = $database->select(<<<'SQL'
            select table_name, column_name, pg_get_serial_sequence(quote_ident(table_schema) || '.' || quote_ident(table_name), column_name) as sequence_name
            from information_schema.columns
            where table_schema = current_schema()
              and pg_get_serial_sequence(quote_ident(table_schema) || '.' || quote_ident(table_name), column_name) is not null
            SQL);

        foreach ($sequences as $sequence) {
            $table = $database->getQueryGrammar()->wrapTable($sequence->table_name);
            $column = $database->getQueryGrammar()->wrap($sequence->column_name);

            $database->select(
                "select setval(?, coalesce((select max({$column}) from {$table}), 0) + 1, false)",
                [$sequence->sequence_name],
            );
        }

        return count($sequences);
    }
}

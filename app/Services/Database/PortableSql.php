<?php

namespace App\Services\Database;

use Illuminate\Support\Facades\DB;

/**
 * Write the few SQL expressions that MySQL, PostgreSQL and SQLite spell
 * differently, for the connection the query runs on.
 *
 * Every column passed in must be a trusted identifier written in code,
 * never user input.
 */
class PortableSql
{
    /**
     * The year and month of a date column, as "YYYY-MM".
     */
    public static function yearMonth(string $column, ?string $connection = null): string
    {
        return match (self::driver($connection)) {
            'pgsql' => "to_char({$column}, 'YYYY-MM')",
            'sqlite' => "strftime('%Y-%m', {$column})",
            default => "date_format({$column}, '%Y-%m')",
        };
    }

    /**
     * Whole days from a date column to a date bound as one "?" parameter.
     *
     * Positive when the bound date is later than the column.
     */
    public static function daysSince(string $column, ?string $connection = null): string
    {
        return match (self::driver($connection)) {
            'pgsql' => "(cast(? as date) - cast({$column} as date))",
            'sqlite' => "cast(julianday(?) - julianday(date({$column})) as integer)",
            default => "datediff(?, {$column})",
        };
    }

    /**
     * The larger of two numeric expressions.
     */
    public static function greatest(string $first, string $second, ?string $connection = null): string
    {
        return match (self::driver($connection)) {
            'sqlite' => "max({$first}, {$second})",
            default => "greatest({$first}, {$second})",
        };
    }

    /**
     * The number of items in a JSON array column.
     */
    public static function jsonArrayLength(string $column, ?string $connection = null): string
    {
        return match (self::driver($connection)) {
            'pgsql' => "json_array_length(cast({$column} as json))",
            'sqlite' => "json_array_length({$column})",
            default => "json_length({$column})",
        };
    }

    private static function driver(?string $connection): string
    {
        return DB::connection($connection)->getDriverName();
    }
}

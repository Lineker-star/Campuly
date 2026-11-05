<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * A person's enrollment in a school is chosen with
     * ofMany(['is_primary' => 'max', 'id' => 'max']): the primary one, else
     * the newest. MySQL and SQLite take max() and min() of a boolean;
     * PostgreSQL has neither, and Laravel's ofMany only accepts MIN and MAX.
     * Define them with PostgreSQL's own bool_or / bool_and state functions,
     * so true wins max() and false wins min(), as elsewhere. Other drivers
     * need nothing.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement(<<<'SQL'
            create or replace aggregate max(boolean) (
                sfunc = boolor_statefunc,
                stype = boolean,
                combinefunc = boolor_statefunc,
                parallel = safe
            )
            SQL);

        DB::statement(<<<'SQL'
            create or replace aggregate min(boolean) (
                sfunc = booland_statefunc,
                stype = boolean,
                combinefunc = booland_statefunc,
                parallel = safe
            )
            SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('drop aggregate if exists max(boolean)');
        DB::statement('drop aggregate if exists min(boolean)');
    }
};

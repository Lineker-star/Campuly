<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * A claim nobody has proved no longer keeps the address from others. Only
     * a proved address is unique, so an organization cannot hold another's
     * web address by claiming it first and never proving it.
     */
    public function up(): void
    {
        // CASE runs on MySQL, PostgreSQL and SQLite. SQLite can only add a
        // virtual generated column to an existing table, and can still index it.
        $expression = 'case when verified_at is null then null else host end';

        Schema::table('school_domains', function (Blueprint $table) use ($expression): void {
            $table->dropUnique(['host']);
            $table->unique(['organization_id', 'host']);

            $column = $table->string('verified_host')->nullable();

            if (Schema::getConnection()->getDriverName() === 'sqlite') {
                $column->virtualAs($expression);
            } else {
                $column->storedAs($expression);
            }

            $table->unique('verified_host');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('school_domains', function (Blueprint $table): void {
            $table->dropUnique(['verified_host']);
            $table->dropColumn('verified_host');
            $table->dropUnique(['organization_id', 'host']);
            $table->unique('host');
        });
    }
};

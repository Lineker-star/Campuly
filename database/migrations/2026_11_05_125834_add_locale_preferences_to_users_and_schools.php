<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * A person may choose the language Campuly speaks to them, and each
     * campus has a default for everyone who has not chosen. Campuses that
     * already exist keep the language the installation was set up in, so an
     * English install does not turn French on upgrade.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('locale', 5)->nullable()->after('email');
        });

        Schema::table('schools', function (Blueprint $table): void {
            $table->string('default_locale', 5)->default('fr')->after('timezone');
        });

        $installed = DB::table('installations')->value('locale');

        if (in_array($installed, ['en', 'fr'], true)) {
            DB::table('schools')->update(['default_locale' => $installed]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table): void {
            $table->dropColumn('default_locale');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('locale');
        });
    }
};

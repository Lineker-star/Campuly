<?php

namespace Tests\Feature;

use App\Models\Promotion;
use App\Models\User;
use App\Services\Database\PortableSql;
use App\Services\Database\SyncIdSequences;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Each expression gives the same answer on MySQL, PostgreSQL and SQLite.
 * CI runs this suite once per engine.
 */
class PortableSqlTest extends TestCase
{
    use RefreshDatabase;

    public function test_year_month_reads_a_date_as_year_dash_month(): void
    {
        $user = User::factory()->create(['created_at' => '2026-03-01 08:30:00']);

        $month = DB::table('users')->where('id', $user->id)
            ->selectRaw(PortableSql::yearMonth('created_at').' as month')
            ->value('month');

        $this->assertSame('2026-03', $month);
    }

    public function test_days_since_counts_whole_days_to_the_bound_date(): void
    {
        $user = User::factory()->create(['created_at' => '2026-03-01 23:59:00']);

        $days = DB::table('users')->where('id', $user->id)
            ->selectRaw(PortableSql::daysSince('created_at').' as days', ['2026-03-11'])
            ->value('days');

        $this->assertSame(10, (int) $days);
    }

    public function test_greatest_returns_the_larger_number(): void
    {
        $user = User::factory()->create();

        $larger = DB::table('users')->where('id', $user->id)
            ->selectRaw(PortableSql::greatest('2 - 7', '0').' as larger')
            ->value('larger');

        $this->assertSame(0, (int) $larger);
    }

    public function test_a_row_written_with_a_fixed_id_does_not_block_the_next_insert(): void
    {
        $fixed = User::factory()->make()->forceFill(['id' => 900001]);
        $fixed->save();

        $moved = app(SyncIdSequences::class)->handle();
        $next = User::factory()->create();

        $this->assertGreaterThan(900001, $next->id);
        $this->assertSame(DB::getDriverName() === 'pgsql', $moved > 0);
    }

    public function test_json_array_length_sorts_by_the_number_of_items(): void
    {
        $three = Promotion::factory()->create(['students' => [1, 2, 3]]);
        $one = Promotion::factory()->create(['students' => [1]]);
        $two = Promotion::factory()->create(['students' => [1, 2]]);

        $order = DB::table('promotions')
            ->whereIn('id', [$three->id, $one->id, $two->id])
            ->orderByRaw(PortableSql::jsonArrayLength('students').' desc')
            ->pluck('id')
            ->all();

        $this->assertSame([$three->id, $two->id, $one->id], $order);
    }
}

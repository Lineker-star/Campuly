<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Traits\StoresDatesWithoutTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use ReflectionClass;
use SplFileInfo;
use Symfony\Component\Finder\Finder;
use Tests\TestCase;

/**
 * A date-only column holds "Y-m-d" on every database, so comparing it with
 * a date string works on SQLite as it does on MySQL and PostgreSQL.
 */
class DateOnlyCastTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_model_with_a_date_only_cast_stores_it_without_a_time(): void
    {
        $missing = [];

        foreach ($this->modelClasses() as $class) {
            /** @var Model $model */
            $model = (new ReflectionClass($class))->newInstanceWithoutConstructor();
            $dateOnly = array_filter(
                $model->getCasts(),
                fn ($cast): bool => is_string($cast) && in_array(explode(':', $cast, 2)[0], ['date', 'immutable_date'], true),
            );

            if ($dateOnly !== [] && !in_array(StoresDatesWithoutTime::class, class_uses_recursive($class), true)) {
                $missing[] = $class;
            }
        }

        $this->assertSame([], $missing, 'These models cast a date without the StoresDatesWithoutTime trait.');
    }

    public function test_a_date_is_written_without_a_time_and_found_by_date(): void
    {
        $year = AcademicYear::factory()->create([
            'starts_on' => Carbon::parse('2026-09-01 14:30:00'),
            'ends_on' => '2027-07-31',
        ]);

        $stored = DB::table('academic_years')->where('id', $year->id)->first(['starts_on', 'ends_on']);

        $this->assertSame('2026-09-01', (string) $stored->starts_on);
        $this->assertSame('2027-07-31', (string) $stored->ends_on);
        $this->assertTrue(AcademicYear::query()->whereKey($year->id)
            ->where('starts_on', '<=', '2026-09-01')
            ->where('ends_on', '>=', '2027-07-31')
            ->exists());
        $this->assertSame('2026-09-01', $year->fresh()->starts_on->toDateString());
    }

    public function test_a_cleared_date_stays_empty(): void
    {
        $year = AcademicYear::factory()->create(['ends_on' => '2027-07-31']);

        $year->update(['ends_on' => null]);

        $this->assertNull(DB::table('academic_years')->where('id', $year->id)->value('ends_on'));
    }

    /**
     * Get every model class in the application.
     *
     * @return array<int, class-string<Model>>
     */
    private function modelClasses(): array
    {
        $classes = [];

        foreach (Finder::create()->files()->name('*.php')->in(app_path('Models')) as $file) {
            /** @var SplFileInfo $file */
            $class = 'App\\Models\\'.Str::before($file->getRelativePathname(), '.php');

            if (!class_exists($class)) {
                continue;
            }

            $reflection = new ReflectionClass($class);

            if ($reflection->isAbstract() || !$reflection->isSubclassOf(Model::class)) {
                continue;
            }

            $classes[] = $class;
        }

        return $classes;
    }
}

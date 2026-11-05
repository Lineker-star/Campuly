<?php

namespace Database\Seeders;

use App\Models\AcademicCycleSection;
use App\Models\Timetable;
use App\Services\Database\SyncIdSequences;
use Illuminate\Database\Seeder;

class TimetableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cycleSection = AcademicCycleSection::query()
            ->where('school_id', 1)
            ->firstOrFail();

        Timetable::firstOrCreate([
            'id' => 1,
        ], [
            'name' => 'Timetable 1',
            'description' => 'Timetable 1 description',
            'academic_cycle_section_id' => $cycleSection->id,
            'academic_period_id' => 1,
        ]);
        // PostgreSQL does not move the id sequence past the fixed id above.
        app(SyncIdSequences::class)->handle();

        Timetable::factory()->count(10)->create();
    }
}

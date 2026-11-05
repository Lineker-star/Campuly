<?php

namespace Database\Seeders;

use App\Models\Exam;
use App\Services\Database\SyncIdSequences;
use Illuminate\Database\Seeder;

class ExamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Exam::firstOrCreate([
            'id' => 1,
            'name' => 'Exam 1',
            'description' => 'Exam 1 description',
            'academic_period_id' => '1',
            'start_date' => '2020-01-01',
            'stop_date' => '2020-01-31',
        ]);

        // PostgreSQL does not move the id sequence past the fixed id above.
        app(SyncIdSequences::class)->handle();

        Exam::factory()->count(10)->create();
    }
}

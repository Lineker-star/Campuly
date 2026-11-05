<?php

namespace Database\Seeders;

use App\Models\Subject;
use App\Services\Database\SyncIdSequences;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Subject::firstOrCreate([
            'id' => 1,
        ], [
            'name' => 'Maths',
            'short_name' => 'mat',
            'school_id' => 1,
        ]);

        // PostgreSQL does not move the id sequence past the fixed id above.
        app(SyncIdSequences::class)->handle();

        Subject::firstOrCreate([
            'name' => 'English',
            'short_name' => 'eng',
            'school_id' => 1,
        ]);
    }
}

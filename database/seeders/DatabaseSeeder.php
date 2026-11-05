<?php

namespace Database\Seeders;

use App\Services\Database\SyncIdSequences;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Several seeders write fixed ids. After each one, PostgreSQL id
     * sequences are moved past them so the next seeder's factories, and the
     * application, do not reuse an id. MySQL and SQLite need nothing.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->seeders() as $seeder) {
            $this->call($seeder);
            app(SyncIdSequences::class)->handle();
        }
    }

    /**
     * The seeders, in the order they run.
     *
     * @return list<class-string<Seeder>>
     */
    private function seeders(): array
    {
        return [
            RunInProductionSeeder::class,
            SchoolSeeder::class,
            UserSeeder::class,
            StudentSeeder::class,
            SubjectSeeder::class,
            AcademicYearSeeder::class,
            AcademicPeriodSeeder::class,
            PromotionSeeder::class,
            SyllabusSeeder::class,
            TimetableSeeder::class,
            ExamSeeder::class,
            NoticeSeeder::class,
            FeeCategorySeeder::class,
            FeeSeeder::class,
            FeeInvoiceSeeder::class,
            FeeInvoiceRecordSeeder::class,
        ];
    }
}

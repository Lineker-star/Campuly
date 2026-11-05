<?php

namespace Tests\Feature;

use App\Enums\NoticeStatus;
use App\Livewire\CreateNoticeForm;
use App\Models\Notice;
use App\Services\School\SchoolService;
use App\Traits\FeatureTestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Files go to the disks the install names, and the app runs on a simple
 * host with the database as its cache, session store and queue.
 */
class StorageAndDriversTest extends TestCase
{
    use FeatureTestTrait;
    use RefreshDatabase;

    public function test_a_school_logo_goes_to_the_public_disk_the_install_names(): void
    {
        config(['filesystems.disks.campus-media' => ['driver' => 'local', 'root' => storage_path('framework/testing/disks/campus-media'), 'url' => 'https://media.example.test']]);
        config(['filesystems.public_disk' => 'campus-media']);
        Storage::fake('campus-media');
        Storage::fake('public');
        $school = $this->workingSchool();

        app(SchoolService::class)->updateSchool($school, ['name' => $school->name, 'address' => $school->address ?? '12 Wharf Road', 'country' => $school->country ?? 'Cameroon', 'state' => $school->state ?? 'Littoral', 'city' => $school->city ?? 'Douala', 'postal_code' => $school->postal_code, 'logo' => UploadedFile::fake()->image('crest.png')]);

        $school->refresh();
        Storage::disk('campus-media')->assertExists($school->logo_path);
        Storage::disk('public')->assertMissing($school->logo_path);
        $this->assertSame(Storage::disk('campus-media')->url($school->logo_path), $school->logo_url);
    }

    public function test_a_notice_attachment_goes_to_the_private_disk_and_remembers_it(): void
    {
        config(['filesystems.disks.vault' => ['driver' => 'local', 'root' => storage_path('framework/testing/disks/vault')]]);
        config(['filesystems.private_disk' => 'vault']);
        Storage::fake('vault');
        $this->authorized_user(['create notice', 'read notice']);

        Livewire::test(CreateNoticeForm::class)
            ->set('title', 'Term letter')
            ->set('content', 'The term starts on Monday.')
            ->set('attachment', UploadedFile::fake()->create('term-letter.pdf', 50, 'application/pdf'))
            ->call('save')
            ->assertHasNoErrors();

        $notice = Notice::query()->where('title', 'Term letter')->latest('id')->firstOrFail();
        $this->assertSame('vault', $notice->attachment_disk);
        Storage::disk('vault')->assertExists($notice->attachment);

        // Moving new files elsewhere later keeps this one downloadable.
        config(['filesystems.private_disk' => 'local']);
        $notice->update(['status' => NoticeStatus::Published, 'active' => true]);

        $this->get(route('notices.attachments.download', $notice))->assertDownload('term-letter.pdf');
    }

    public function test_an_attachment_on_a_disk_the_install_no_longer_has_is_not_served(): void
    {
        $this->authorized_user(['read notice']);
        $notice = Notice::create([
            'title' => 'Old letter',
            'content' => 'Kept on a disk that is gone.',
            'start_date' => now()->toDateString(),
            'stop_date' => now()->addWeek()->toDateString(),
            'school_id' => $this->workingSchool()->id,
            'status' => NoticeStatus::Published,
            'active' => true,
            'attachment' => 'notice-attachments/old-letter.pdf',
            'attachment_disk' => 'retired-disk',
            'attachment_name' => 'old-letter.pdf',
        ]);

        $this->assertFalse($notice->hasManagedAttachment());
        $this->get(route('notices.attachments.download', $notice))->assertNotFound();
    }

    public function test_the_database_cache_store_keeps_values(): void
    {
        config(['cache.default' => 'database']);

        Cache::put('campuly-check', 'stored', 60);

        $this->assertSame('stored', Cache::get('campuly-check'));
        $this->assertSame(1, DB::table('cache')->where('key', 'like', '%campuly-check')->count());
    }

    public function test_the_database_queue_holds_jobs_until_a_worker_takes_them(): void
    {
        config(['queue.default' => 'database']);

        Queue::connection('database')->pushRaw(json_encode(['job' => 'campuly-check', 'data' => []]), 'default');

        $this->assertSame(1, DB::table('jobs')->where('queue', 'default')->count());
        $this->assertSame(1, Queue::connection('database')->size('default'));
    }

    public function test_the_database_session_store_keeps_a_visitor_session(): void
    {
        config(['session.driver' => 'database']);

        $this->get(route('login'))->assertOk();

        $this->assertSame(1, DB::table('sessions')->count());
    }

    public function test_every_documented_mail_transport_is_configured(): void
    {
        foreach (['smtp', 'mailgun', 'resend', 'postmark', 'ses', 'log', 'array'] as $mailer) {
            $this->assertIsArray(config("mail.mailers.$mailer"), "Mailer [$mailer] is not configured.");
        }

        $this->assertArrayHasKey('key', config('services.resend'));
    }
}

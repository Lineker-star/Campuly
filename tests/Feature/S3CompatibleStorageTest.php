<?php

namespace Tests\Feature;

use App\Services\School\SchoolService;
use App\Traits\FeatureTestTrait;
use Aws\S3\S3Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Files round-trip through a real S3-compatible server (Adobe S3Mock in CI; MinIO works too).
 *
 * Runs only when S3_TEST_ENDPOINT is set, for example:
 *   S3_TEST_ENDPOINT=http://127.0.0.1:9000 S3_TEST_KEY=campuly S3_TEST_SECRET=campuly-secret
 * The bucket is created when missing. Without the variables the test is skipped.
 */
class S3CompatibleStorageTest extends TestCase
{
    use FeatureTestTrait;
    use RefreshDatabase;

    private string $bucket;

    protected function setUp(): void
    {
        parent::setUp();

        $endpoint = getenv('S3_TEST_ENDPOINT');

        if (!is_string($endpoint) || $endpoint === '') {
            $this->markTestSkipped('Set S3_TEST_ENDPOINT to run the S3-compatible storage test.');
        }

        $this->bucket = (string) (getenv('S3_TEST_BUCKET') ?: 'campuly-test');
        $settings = [
            'key' => (string) getenv('S3_TEST_KEY'),
            'secret' => (string) getenv('S3_TEST_SECRET'),
            'region' => 'us-east-1',
            'bucket' => $this->bucket,
            'endpoint' => $endpoint,
            'use_path_style_endpoint' => true,
            'url' => rtrim($endpoint, '/').'/'.$this->bucket,
        ];

        config([
            'filesystems.disks.s3' => ['driver' => 's3', 'visibility' => 'private', 'throw' => true, ...$settings],
            'filesystems.disks.s3-public' => ['driver' => 's3', 'visibility' => 'private', 'throw' => true, ...$settings],
        ]);

        $client = new S3Client([
            'version' => 'latest',
            'region' => 'us-east-1',
            'endpoint' => $endpoint,
            'use_path_style_endpoint' => true,
            'credentials' => ['key' => $settings['key'], 'secret' => $settings['secret']],
        ]);

        if (!$client->doesBucketExist($this->bucket)) {
            $client->createBucket(['Bucket' => $this->bucket]);
        }
    }

    public function test_a_private_file_is_written_read_and_deleted(): void
    {
        $path = 'checks/'.Str::random(12).'.txt';
        $disk = Storage::disk('s3');

        $disk->put($path, 'Campuly storage check');

        $this->assertTrue($disk->exists($path));
        $this->assertSame('Campuly storage check', $disk->get($path));
        $this->assertStringContainsString('X-Amz-Signature', $disk->temporaryUrl($path, now()->addMinutes(5)));

        $disk->delete($path);
        $this->assertFalse($disk->exists($path));
    }

    public function test_a_school_logo_upload_lands_in_the_bucket(): void
    {
        config(['filesystems.public_disk' => 's3-public']);
        $school = $this->workingSchool();

        app(SchoolService::class)->updateSchool($school, ['name' => $school->name, 'address' => $school->address ?? '12 Wharf Road', 'country' => $school->country ?? 'Cameroon', 'state' => $school->state ?? 'Littoral', 'city' => $school->city ?? 'Douala', 'postal_code' => $school->postal_code, 'logo' => UploadedFile::fake()->image('crest.png')]);

        $school->refresh();
        $this->assertTrue(Storage::disk('s3-public')->exists($school->logo_path));
        $this->assertStringContainsString('/'.$this->bucket.'/'.$school->logo_path, $school->logo_url);

        Storage::disk('s3-public')->delete($school->logo_path);
    }
}

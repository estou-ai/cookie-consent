<?php

namespace Estouai\CookieConsent\Tests\Console;

use Estouai\CookieConsent\Tests\TestCase;
use Illuminate\Support\Facades\File;

class ConsentLogCommandsTest extends TestCase
{
    protected function tearDown(): void
    {
        File::deleteDirectory(storage_path('app/cookie-consent'));
        File::delete(storage_path('app/consent-export.csv'));

        parent::tearDown();
    }

    protected function writeLog(string $site, string $date, array $entries): string
    {
        $path = storage_path("app/cookie-consent/{$site}/{$date}.jsonl");
        File::ensureDirectoryExists(dirname($path));

        File::put($path, implode('', array_map(
            fn ($entry) => json_encode($entry)."\n",
            $entries
        )));

        return $path;
    }

    public function test_prune_deletes_only_files_past_retention()
    {
        $old = $this->writeLog('default', now()->subDays(40)->format('Y-m-d'), [['version' => 1]]);
        $recent = $this->writeLog('default', now()->subDays(5)->format('Y-m-d'), [['version' => 1]]);

        $this->artisan('cookie-consent:prune', ['--days' => 30])->assertSuccessful();

        $this->assertFileDoesNotExist($old);
        $this->assertFileExists($recent);
    }

    public function test_prune_dry_run_deletes_nothing()
    {
        $old = $this->writeLog('default', now()->subDays(40)->format('Y-m-d'), [['version' => 1]]);

        $this->artisan('cookie-consent:prune', ['--days' => 30, '--dry-run' => true])->assertSuccessful();

        $this->assertFileExists($old);
    }

    public function test_prune_rejects_a_zero_day_window()
    {
        $this->artisan('cookie-consent:prune', ['--days' => 0])->assertFailed();
    }

    public function test_export_writes_a_csv_within_the_date_range()
    {
        $this->writeLog('default', '2026-01-01', [
            ['timestamp' => '2026-01-01T10:00:00+00:00', 'site' => 'default', 'version' => 1, 'groups' => ['necessary', 'analytics'], 'source' => 'explicit', 'page' => '/precos'],
        ]);
        $this->writeLog('default', '2026-03-01', [['timestamp' => 'x', 'version' => 2, 'groups' => []]]);

        $path = storage_path('app/consent-export.csv');

        $this->artisan('cookie-consent:export', [
            'path' => $path,
            '--to' => '2026-02-01',
        ])->assertSuccessful();

        $csv = File::get($path);
        $this->assertStringContainsString('timestamp,site,version,groups', $csv);
        $this->assertStringContainsString('"necessary analytics"', $csv);
        $this->assertStringContainsString('/precos', $csv);
        // outside the range
        $this->assertStringNotContainsString(',2,', $csv);
    }

    public function test_prune_is_scheduled_daily()
    {
        $events = app(\Illuminate\Console\Scheduling\Schedule::class)->events();

        $this->assertNotEmpty(array_filter(
            $events,
            fn ($event) => str_contains($event->command ?? '', 'cookie-consent:prune')
                && $event->expression === '0 0 * * *'
        ));
    }

    public function test_export_filters_by_site()
    {
        $this->writeLog('default', '2026-01-01', [['timestamp' => 'a', 'site' => 'default', 'version' => 1]]);
        $this->writeLog('fr', '2026-01-01', [['timestamp' => 'b', 'site' => 'fr', 'version' => 1]]);

        $path = storage_path('app/consent-export.csv');

        $this->artisan('cookie-consent:export', ['path' => $path, '--site' => 'fr'])->assertSuccessful();

        $csv = File::get($path);
        $this->assertStringContainsString('fr', $csv);
        $this->assertStringNotContainsString('default', $csv);
    }
}

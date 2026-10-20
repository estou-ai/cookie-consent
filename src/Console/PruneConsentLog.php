<?php

namespace Estouai\CookieConsent\Console;

use Estouai\CookieConsent\ConsentLog\ConsentLogger;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

// Storage limitation — RGPD Art. 5º(1)(e) / LGPD Art. 15º: proof of consent
// may only be kept as long as it is needed. Schedule this daily.
class PruneConsentLog extends Command
{
    protected $signature = 'cookie-consent:prune
        {--days= : Override the configured retention window}
        {--dry-run : List what would be deleted without deleting it}';

    protected $description = 'Delete consent log files past the retention window';

    public function handle(ConsentLogger $logger)
    {
        $days = (int) ($this->option('days') ?? config('cookie-consent.log.retention_days'));

        if ($days < 1) {
            $this->error('Retention must be at least 1 day.');

            return self::FAILURE;
        }

        $cutoff = now()->subDays($days)->format('Y-m-d');
        $deleted = 0;

        foreach ($logger->files() as $path => $date) {
            if ($date >= $cutoff) {
                continue;
            }

            $this->line(($this->option('dry-run') ? 'Would delete ' : 'Deleted ').$date);

            if (! $this->option('dry-run')) {
                File::delete($path);
            }

            $deleted++;
        }

        $this->info("{$deleted} file(s) older than {$cutoff}.");

        return self::SUCCESS;
    }
}

<?php

namespace Estouai\CookieConsent\Console;

use Estouai\CookieConsent\ConsentLog\ConsentLogger;
use Illuminate\Console\Command;

// Produces the accountability record a regulator or legal team asks for —
// RGPD Art. 7º(1) / LGPD Art. 8º. CSV because that is what they open.
class ExportConsentLog extends Command
{
    protected $signature = 'cookie-consent:export
        {path : Where to write the CSV}
        {--from= : Earliest day to include (Y-m-d)}
        {--to= : Latest day to include (Y-m-d)}
        {--site= : Only this site handle}';

    protected $description = 'Export the consent log to CSV';

    protected array $columns = ['timestamp', 'site', 'version', 'groups', 'source', 'page', 'ip_hash', 'user_agent'];

    public function handle(ConsentLogger $logger)
    {
        $from = $this->option('from');
        $to = $this->option('to');
        $site = $this->option('site');

        $handle = fopen($this->argument('path'), 'w');

        if ($handle === false) {
            $this->error('Could not write to '.$this->argument('path'));

            return self::FAILURE;
        }

        fputcsv($handle, $this->columns);
        $rows = 0;

        foreach ($logger->files() as $path => $date) {
            if (($from && $date < $from) || ($to && $date > $to)) {
                continue;
            }

            if ($site && basename(dirname($path)) !== $site) {
                continue;
            }

            foreach ($logger->entries($path) as $entry) {
                fputcsv($handle, array_map(function ($column) use ($entry) {
                    $value = $entry[$column] ?? '';

                    return is_array($value) ? implode(' ', $value) : $value;
                }, $this->columns));

                $rows++;
            }
        }

        fclose($handle);

        $this->info("{$rows} entries exported to ".$this->argument('path').'.');

        return self::SUCCESS;
    }
}

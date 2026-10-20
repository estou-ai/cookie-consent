<?php

namespace Estouai\CookieConsent\ConsentLog;

use Illuminate\Support\Facades\File;

// Append-only, one JSON line per consent decision — the accountability
// record RGPD Art. 7º(1) / LGPD Art. 8º require ("must be able to
// demonstrate that the data subject has consented"). Deliberately not a
// database table: a site this addon targets may not have one configured,
// and the only real query need is "this visitor" / "this day", which
// grep/jq already handle fine against JSONL — see README's "Consent log".
class ConsentLogger
{
    public function record(array $entry): void
    {
        $site = $entry['site'] ?? 'default';
        $date = now()->format('Y-m-d');
        $path = storage_path("app/cookie-consent/{$site}/{$date}.jsonl");

        File::ensureDirectoryExists(dirname($path));
        File::append($path, json_encode($entry, JSON_UNESCAPED_UNICODE)."\n");
    }

    /**
     * Every log file, oldest first, as [path => Y-m-d]. The day is the file
     * name, so retention and date ranges never need to open a file.
     */
    public function files(): array
    {
        $files = [];

        foreach (File::glob(storage_path('app/cookie-consent/*/*.jsonl')) ?: [] as $path) {
            $date = basename($path, '.jsonl');

            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                $files[$path] = $date;
            }
        }

        asort($files);

        return $files;
    }

    /** Stream the entries of one day's file, so a year never lands in memory. */
    public function entries(string $path): \Generator
    {
        $file = new \SplFileObject($path);

        while (! $file->eof()) {
            $line = trim((string) $file->fgets());

            if ($line !== '' && ($entry = json_decode($line, true)) !== null) {
                yield $entry;
            }
        }
    }
}

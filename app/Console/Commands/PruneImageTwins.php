<?php

namespace App\Console\Commands;

use App\Support\MediaStore;
use Illuminate\Console\Command;

class PruneImageTwins extends Command
{
    protected $signature = 'media:prune-png {--force : actually delete (default is a dry run)}';

    protected $description = 'Delete leftover PNG twins of uploaded images (the site serves WebP only)';

    public function handle(): int
    {
        [$files, $bytes] = MediaStore::prunePngTwins(! $this->option('force'), function (string $name, int $size) {
            $this->line(sprintf('  %-60s %6.1f MB', $name, $size / 1048576));
        });
        $mb = number_format($bytes / 1048576, 1);

        $this->info($this->option('force')
            ? "Removed $files PNG file(s), freed $mb MB."
            : "Dry run: $files PNG file(s) ($mb MB) can be removed. Run with --force to delete them.");

        return self::SUCCESS;
    }
}

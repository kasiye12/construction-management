<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DatabaseBackupService;

class DatabaseBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup 
                            {--type=daily : Backup classification (daily, manual, auto)}
                            {--compress : Whether to compress the dump with gzip}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform a full MySQL database backup and synchronize canonical dump files';

    /**
     * Execute the console command.
     */
    public function handle(DatabaseBackupService $backupService): int
    {
        $type = $this->option('type') ?: 'daily';
        $compress = (bool) $this->option('compress');

        $this->info("🔄 Initiating {$type} database backup...");

        try {
            $result = $backupService->createBackup($type, $compress, 1);

            $this->newLine();
            $this->info("✅ Database backup completed successfully!");
            $this->table(
                ['Property', 'Value'],
                [
                    ['File', $result['filename']],
                    ['Size', $result['filesize_formatted']],
                    ['Type', $result['type']],
                    ['Duration', $result['duration'] . ' seconds'],
                    ['Document Dump', $result['canonical_doc_dump']],
                    ['Repository Dump', $result['canonical_repo_dump']],
                    ['Pruned Old Files', $result['pruned_count']],
                ]
            );

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("❌ Database backup failed: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

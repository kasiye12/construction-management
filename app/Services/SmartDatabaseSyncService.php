<?php

namespace App\Services;

use App\Models\DatabaseSyncLog;
use App\Models\ActivityLog;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SmartDatabaseSyncService
{
    protected string $stagingDbName;
    protected string $prodDbName;
    protected string $syncUploadsDir;
    protected DatabaseBackupService $backupService;

    public function __construct(DatabaseBackupService $backupService)
    {
        $this->backupService = $backupService;
        $dbConfig = config('database.connections.mysql');
        $this->prodDbName = $dbConfig['database'] ?? 'construction_management';
        $this->stagingDbName = $this->prodDbName . '_sync_staging';

        $this->syncUploadsDir = storage_path('app/sync_uploads');
        if (!File::isDirectory($this->syncUploadsDir)) {
            File::makeDirectory($this->syncUploadsDir, 0755, true, true);
        }
    }

    /**
     * Parse and compare an uploaded SQL file with the production database.
     * Does NOT touch production data. Runs safely inside an isolated staging database.
     *
     * @param UploadedFile|string $file
     * @param int|null $userId
     * @return DatabaseSyncLog
     */
    public function analyzeSqlFile($file, ?int $userId = null): DatabaseSyncLog
    {
        $originalFilename = ($file instanceof UploadedFile) ? $file->getClientOriginalName() : basename($file);
        $fileSize = ($file instanceof UploadedFile) ? $file->getSize() : filesize($file);
        $batchId = 'sync_' . date('Ymd_His') . '_' . Str::lower(Str::random(6));

        // Store file safely in storage/app/sync_uploads/
        $storedFilename = "{$batchId}_{$originalFilename}";
        $storedPath = "{$this->syncUploadsDir}/{$storedFilename}";

        if ($file instanceof UploadedFile) {
            $file->move($this->syncUploadsDir, $storedFilename);
        } else {
            @copy($file, $storedPath);
        }

        // 1. Prepare Staging Database
        $this->createStagingDatabase();

        try {
            // 2. Load uploaded SQL into Staging Database
            $this->loadSqlIntoStaging($storedPath);

            // 3. Compare Staging DB with Production DB
            $comparison = $this->performComparison();

            $effectiveUserId = $userId ?: Auth::id();
            $adminUser = $effectiveUserId ? \App\Models\User::find($effectiveUserId) : null;
            $adminName = $adminUser ? $adminUser->name : 'System Administrator';

            // 4. Save Preview in DatabaseSyncLog
            $syncLog = DatabaseSyncLog::create([
                'batch_id' => $batchId,
                'uploaded_filename' => $originalFilename,
                'file_path' => $storedPath,
                'file_size' => $fileSize,
                'user_id' => $effectiveUserId,
                'admin_name' => $adminName,
                'status' => 'previewed',
                'tables_created_count' => count($comparison['tables_to_create']),
                'columns_added_count' => count($comparison['columns_to_add']),
                'records_inserted_count' => $comparison['total_records_to_insert'],
                'records_updated_count' => $comparison['total_records_to_update'],
                'records_unchanged_count' => $comparison['total_records_unchanged'],
                'tables_created' => $comparison['tables_to_create'],
                'columns_added' => $comparison['columns_to_add'],
                'changes_summary' => $comparison['tables_summary'],
                'incompatible_warnings' => $comparison['warnings'],
            ]);

            return $syncLog;
        } catch (\Throwable $e) {
            $this->dropStagingDatabase();
            throw $e;
        }
    }

    /**
     * Perform the live non-destructive incremental synchronization after admin confirmation.
     *
     * @param DatabaseSyncLog $syncLog
     * @param array $options Selectable sync options: [create_tables, add_columns, insert_records, update_records]
     * @param int|null $userId
     * @return array
     */
    public function applySync(DatabaseSyncLog $syncLog, array $options = [], ?int $userId = null): array
    {
        if ($syncLog->status !== 'previewed') {
            throw new \Exception("Cannot apply sync batch '{$syncLog->batch_id}' because its status is '{$syncLog->status}'.");
        }

        $optCreateTables = $options['create_tables'] ?? true;
        $optAddColumns = $options['add_columns'] ?? true;
        $optInsertRecords = $options['insert_records'] ?? true;
        $optUpdateRecords = $options['update_records'] ?? true;

        // 1. Mandatory Pre-Sync Backup for 100% Rollback Safety
        $backupResult = $this->backupService->createBackup('pre_sync', false, $userId);
        $syncLog->update(['backup_file' => $backupResult['path']]);

        // 2. Ensure Staging Database is Populated
        $this->ensureStagingPopulated($syncLog->file_path);

        $changesSummary = $syncLog->changes_summary ?? [];
        $tablesToCreate = $syncLog->tables_created ?? [];
        $columnsToAdd = $syncLog->columns_added ?? [];

        $appliedTablesCreated = 0;
        $appliedColumnsAdded = 0;
        $appliedRecordsInserted = 0;
        $appliedRecordsUpdated = 0;

        $dbConfig = config('database.connections.mysql');
        $dbUser = $dbConfig['username'] ?? 'root';
        $dbPass = $dbConfig['password'] ?? '';
        $dbHost = $dbConfig['host'] ?? '127.0.0.1';
        $dbPort = $dbConfig['port'] ?? '3306';

        $pdo = DB::connection()->getPdo();

        try {
            // Disable foreign key checks during incremental structural and data merge
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

            // A. Create Missing Tables
            if ($optCreateTables && !empty($tablesToCreate)) {
                foreach ($tablesToCreate as $tblInfo) {
                    $tblName = $tblInfo['table_name'];
                    // Get DDL from staging
                    $createRow = DB::select("SHOW CREATE TABLE `{$this->stagingDbName}`.`{$tblName}`");
                    if (!empty($createRow)) {
                        $createSql = $createRow[0]->{'Create Table'} ?? null;
                        if ($createSql) {
                            // Ensure CREATE TABLE IF NOT EXISTS
                            $safeCreateSql = preg_replace('/^CREATE TABLE/i', 'CREATE TABLE IF NOT EXISTS', $createSql);
                            // Strip any database qualification
                            $safeCreateSql = str_replace("`{$this->stagingDbName}`.", "", $safeCreateSql);
                            DB::statement($safeCreateSql);
                            $appliedTablesCreated++;
                        }
                    }
                }
            }

            // B. Add Missing Columns
            if ($optAddColumns && !empty($columnsToAdd)) {
                foreach ($columnsToAdd as $colInfo) {
                    $tblName = $colInfo['table_name'];
                    $colName = $colInfo['column_name'];
                    $colDef = $colInfo['column_definition'];
                    $afterCol = $colInfo['after_column'] ?? null;

                    // Check if column already exists in production
                    $existsInProd = DB::select("
                        SELECT 1 FROM information_schema.COLUMNS 
                        WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?
                    ", [$this->prodDbName, $tblName, $colName]);

                    if (empty($existsInProd)) {
                        $alterSql = "ALTER TABLE `{$this->prodDbName}`.`{$tblName}` ADD COLUMN `{$colName}` {$colDef}";
                        if ($afterCol) {
                            $alterSql .= " AFTER `{$afterCol}`";
                        }
                        DB::statement($alterSql);
                        $appliedColumnsAdded++;
                    }
                }
            }

            // C. Insert & Update Data
            foreach ($changesSummary as $tblSummary) {
                $tblName = $tblSummary['table_name'];
                $isNewTable = $tblSummary['is_new_table'] ?? false;

                // If table is newly created and we allow inserting records
                if ($isNewTable && $optInsertRecords) {
                    $count = (int) ($tblSummary['records_to_insert'] ?? 0);
                    if ($count > 0) {
                        // Insert all rows from staging to prod
                        DB::statement("INSERT IGNORE INTO `{$this->prodDbName}`.`{$tblName}` SELECT * FROM `{$this->stagingDbName}`.`{$tblName}`");
                        $appliedRecordsInserted += $count;
                    }
                    continue;
                }

                if (!$isNewTable) {
                    $pkColumns = $tblSummary['primary_key'] ?? [];
                    $commonColumns = $tblSummary['common_columns'] ?? [];

                    if (empty($commonColumns)) continue;

                    // 1. Insert New Records
                    if ($optInsertRecords && ($tblSummary['records_to_insert'] ?? 0) > 0 && !empty($pkColumns)) {
                        $colListSql = implode('`, `', $commonColumns);

                        $joinCondition = [];
                        foreach ($pkColumns as $pk) {
                            $joinCondition[] = "s.`{$pk}` = p.`{$pk}`";
                        }
                        $joinSql = implode(' AND ', $joinCondition);

                        $nullCondition = [];
                        foreach ($pkColumns as $pk) {
                            $nullCondition[] = "p.`{$pk}` IS NULL";
                        }
                        $whereNullSql = implode(' AND ', $nullCondition);

                        $insertSql = "
                            INSERT INTO `{$this->prodDbName}`.`{$tblName}` (`{$colListSql}`)
                            SELECT s.`" . implode('`, s.`', $commonColumns) . "`
                            FROM `{$this->stagingDbName}`.`{$tblName}` s
                            LEFT JOIN `{$this->prodDbName}`.`{$tblName}` p ON {$joinSql}
                            WHERE {$whereNullSql}
                        ";

                        $inserted = DB::affectingStatement($insertSql);
                        $appliedRecordsInserted += $inserted;
                    }

                    // 2. Update Matching Differing Records
                    if ($optUpdateRecords && ($tblSummary['records_to_update'] ?? 0) > 0 && !empty($pkColumns)) {
                        // Exclude primary key from update setters
                        $updateCols = array_diff($commonColumns, $pkColumns);
                        if (!empty($updateCols)) {
                            $joinCondition = [];
                            foreach ($pkColumns as $pk) {
                                $joinCondition[] = "s.`{$pk}` = p.`{$pk}`";
                            }
                            $joinSql = implode(' AND ', $joinCondition);

                            $setStatements = [];
                            $diffCondition = [];
                            foreach ($updateCols as $c) {
                                $setStatements[] = "p.`{$c}` = s.`{$c}`";
                                $diffCondition[] = "NOT (p.`{$c}` <=> s.`{$c}`)";
                            }

                            $setSql = implode(', ', $setStatements);
                            $whereDiffSql = implode(' OR ', $diffCondition);

                            $updateSql = "
                                UPDATE `{$this->prodDbName}`.`{$tblName}` p
                                JOIN `{$this->stagingDbName}`.`{$tblName}` s ON {$joinSql}
                                SET {$setSql}
                                WHERE {$whereDiffSql}
                            ";

                            $updated = DB::affectingStatement($updateSql);
                            $appliedRecordsUpdated += $updated;
                        }
                    }
                }
            }

            // Re-enable foreign key checks
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

            // Drop staging database now that synchronization is complete
            $this->dropStagingDatabase();

            // Update Sync Log Status
            $syncLog->update([
                'status' => 'applied',
                'tables_created_count' => $appliedTablesCreated,
                'columns_added_count' => $appliedColumnsAdded,
                'records_inserted_count' => $appliedRecordsInserted,
                'records_updated_count' => $appliedRecordsUpdated,
                'applied_at' => now(),
            ]);

            // Synchronize canonical backups
            $this->backupService->createBackup('post_sync', false, $userId);

            // Log Activity
            $effectiveUserId = $userId ?: Auth::id();
            if ($effectiveUserId) {
                ActivityLog::create([
                    'user_id' => $effectiveUserId,
                    'action' => 'database_synchronized',
                    'module' => 'SmartDatabaseSync',
                    'description' => "Smart DB Sync applied: {$appliedTablesCreated} tables created, {$appliedColumnsAdded} columns added, {$appliedRecordsInserted} records inserted, {$appliedRecordsUpdated} records updated.",
                    'new_values' => json_encode([
                        'batch_id' => $syncLog->batch_id,
                        'tables_created' => $appliedTablesCreated,
                        'columns_added' => $appliedColumnsAdded,
                        'records_inserted' => $appliedRecordsInserted,
                        'records_updated' => $appliedRecordsUpdated,
                        'backup_file' => $syncLog->backup_file,
                    ]),
                    'ip_address' => request()->ip() ?? '127.0.0.1',
                ]);
            }

            return [
                'success' => true,
                'batch_id' => $syncLog->batch_id,
                'tables_created' => $appliedTablesCreated,
                'columns_added' => $appliedColumnsAdded,
                'records_inserted' => $appliedRecordsInserted,
                'records_updated' => $appliedRecordsUpdated,
                'backup_file' => $syncLog->backup_file,
            ];
        } catch (\Throwable $e) {
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
            $syncLog->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Rollback a previously applied database synchronization using its pre-sync backup.
     *
     * @param DatabaseSyncLog $syncLog
     * @param int|null $userId
     * @return array
     */
    public function rollbackSync(DatabaseSyncLog $syncLog, ?int $userId = null): array
    {
        if (!$syncLog->canRollback()) {
            throw new \Exception("Cannot rollback batch '{$syncLog->batch_id}'. Valid backup file not found.");
        }

        $backupPath = $syncLog->backup_file;

        $dbConfig = config('database.connections.mysql');
        $dbUser = $dbConfig['username'] ?? 'root';
        $dbPass = $dbConfig['password'] ?? '';
        $dbHost = $dbConfig['host'] ?? '127.0.0.1';
        $dbPort = $dbConfig['port'] ?? '3306';

        // Write secure temporary options file
        $tempCnf = tempnam(sys_get_temp_dir(), 'my_rb_');
        $cnfContent = "[client]\nuser=\"{$dbUser}\"\npassword=\"{$dbPass}\"\nhost=\"{$dbHost}\"\nport=\"{$dbPort}\"\n";
        file_put_contents($tempCnf, $cnfContent);
        chmod($tempCnf, 0600);

        try {
            // Drop any tables that were newly created by this specific sync batch
            $tablesCreated = $syncLog->tables_created ?? [];
            if (!empty($tablesCreated)) {
                DB::statement("SET FOREIGN_KEY_CHECKS = 0");
                foreach ($tablesCreated as $tbl) {
                    $tblName = is_array($tbl) ? ($tbl['table_name'] ?? null) : $tbl;
                    if ($tblName && $tblName !== 'database_sync_logs' && $tblName !== 'migrations') {
                        DB::statement("DROP TABLE IF EXISTS `{$this->prodDbName}`.`{$tblName}`");
                    }
                }
                DB::statement("SET FOREIGN_KEY_CHECKS = 1");
            }

            // Restore database from backup file
            $cmd = sprintf(
                'mysql --defaults-extra-file=%s %s < %s',
                escapeshellarg($tempCnf),
                escapeshellarg($this->prodDbName),
                escapeshellarg($backupPath)
            );

            $output = [];
            $exitCode = 0;
            exec($cmd . ' 2>&1', $output, $exitCode);

            if ($exitCode !== 0) {
                throw new \Exception("Rollback failed: " . implode("\n", $output));
            }

            $syncLog->update([
                'status' => 'rolled_back',
                'backup_file' => $backupPath,
                'rolled_back_at' => now(),
            ]);

            // Refresh canonical dumps
            $this->backupService->createBackup('post_rollback', false, $userId);

            // Log activity
            $effectiveUserId = $userId ?: Auth::id();
            if ($effectiveUserId) {
                ActivityLog::create([
                    'user_id' => $effectiveUserId,
                    'action' => 'database_rollback',
                    'module' => 'SmartDatabaseSync',
                    'description' => "Database rolled back for batch {$syncLog->batch_id} using {$backupPath}.",
                    'new_values' => json_encode(['batch_id' => $syncLog->batch_id, 'backup_file' => $backupPath]),
                    'ip_address' => request()->ip() ?? '127.0.0.1',
                ]);
            }

            return [
                'success' => true,
                'batch_id' => $syncLog->batch_id,
                'backup_file' => $backupPath,
            ];
        } finally {
            if (file_exists($tempCnf)) {
                @unlink($tempCnf);
            }
        }
    }

    /**
     * Compare staging database against production database in detail.
     */
    protected function performComparison(): array
    {
        // 1. Get Staging & Production Tables
        $stagingTables = $this->getTablesList($this->stagingDbName);
        $prodTables = $this->getTablesList($this->prodDbName);

        $tablesToCreate = [];
        $columnsToAdd = [];
        $tablesSummary = [];
        $warnings = [];

        $totalRecordsToInsert = 0;
        $totalRecordsToUpdate = 0;
        $totalRecordsUnchanged = 0;

        foreach ($stagingTables as $tblName) {
            $isNewTable = !in_array($tblName, $prodTables);

            if ($isNewTable) {
                // Table does not exist in production
                $rowCount = (int) DB::table("{$this->stagingDbName}.{$tblName}")->count();
                $tablesToCreate[] = [
                    'table_name' => $tblName,
                    'rows_count' => $rowCount,
                ];

                $tablesSummary[$tblName] = [
                    'table_name' => $tblName,
                    'is_new_table' => true,
                    'new_columns_count' => 0,
                    'new_columns' => [],
                    'records_to_insert' => $rowCount,
                    'records_to_update' => 0,
                    'records_unchanged' => 0,
                    'primary_key' => $this->getPrimaryKeyColumns($this->stagingDbName, $tblName),
                ];

                $totalRecordsToInsert += $rowCount;
            } else {
                // Table exists in both -> compare columns & data
                $stagingCols = $this->getColumnsDetails($this->stagingDbName, $tblName);
                $prodCols = $this->getColumnsDetails($this->prodDbName, $tblName);

                $newColsForTable = [];
                $prevCol = null;

                foreach ($stagingCols as $colName => $colData) {
                    if (!isset($prodCols[$colName])) {
                        // Column missing in production
                        $colDef = $this->buildColumnDefinition($colData);
                        $colEntry = [
                            'table_name' => $tblName,
                            'column_name' => $colName,
                            'column_type' => $colData['type'],
                            'column_definition' => $colDef,
                            'after_column' => $prevCol,
                        ];
                        $columnsToAdd[] = $colEntry;
                        $newColsForTable[] = $colEntry;
                    } else {
                        // Check for type incompatibility
                        $stagingType = strtolower($colData['type']);
                        $prodType = strtolower($prodCols[$colName]['type']);
                        if ($stagingType !== $prodType) {
                            $warnings[] = [
                                'type' => 'column_type_mismatch',
                                'table_name' => $tblName,
                                'column_name' => $colName,
                                'prod_type' => $prodType,
                                'staging_type' => $stagingType,
                                'message' => "Column `{$colName}` in `{$tblName}` has type '{$prodType}' in production but '{$stagingType}' in uploaded file.",
                            ];
                        }
                    }
                    $prevCol = $colName;
                }

                // Data Comparison for existing table
                $pkCols = $this->getPrimaryKeyColumns($this->stagingDbName, $tblName);
                $commonCols = array_values(array_intersect(array_keys($stagingCols), array_keys($prodCols)));

                $toInsert = 0;
                $toUpdate = 0;
                $unchanged = 0;

                if (!empty($pkCols)) {
                    $joinCond = [];
                    foreach ($pkCols as $pk) {
                        $joinCond[] = "s.`{$pk}` = p.`{$pk}`";
                    }
                    $joinSql = implode(' AND ', $joinCond);

                    // 1. Records in staging not in production (To Insert)
                    $nullCond = [];
                    foreach ($pkCols as $pk) {
                        $nullCond[] = "p.`{$pk}` IS NULL";
                    }
                    $whereNullSql = implode(' AND ', $nullCond);

                    $insertRes = DB::select("
                        SELECT COUNT(*) as cnt 
                        FROM `{$this->stagingDbName}`.`{$tblName}` s 
                        LEFT JOIN `{$this->prodDbName}`.`{$tblName}` p ON {$joinSql} 
                        WHERE {$whereNullSql}
                    ");
                    $toInsert = (int) ($insertRes[0]->cnt ?? 0);

                    // 2. Records present in both -> compare common columns
                    $updateCols = array_diff($commonCols, $pkCols);
                    if (!empty($updateCols)) {
                        $diffCond = [];
                        foreach ($updateCols as $c) {
                            $diffCond[] = "NOT (p.`{$c}` <=> s.`{$c}`)";
                        }
                        $whereDiffSql = implode(' OR ', $diffCond);

                        $updateRes = DB::select("
                            SELECT COUNT(*) as cnt 
                            FROM `{$this->stagingDbName}`.`{$tblName}` s 
                            JOIN `{$this->prodDbName}`.`{$tblName}` p ON {$joinSql} 
                            WHERE {$whereDiffSql}
                        ");
                        $toUpdate = (int) ($updateRes[0]->cnt ?? 0);
                    }

                    // 3. Unchanged records
                    $totalStagingMatching = DB::select("
                        SELECT COUNT(*) as cnt 
                        FROM `{$this->stagingDbName}`.`{$tblName}` s 
                        JOIN `{$this->prodDbName}`.`{$tblName}` p ON {$joinSql}
                    ");
                    $matchCount = (int) ($totalStagingMatching[0]->cnt ?? 0);
                    $unchanged = max(0, $matchCount - $toUpdate);
                } else {
                    // No primary key: count staging rows as unchanged or insert if missing
                    $stagingCount = (int) DB::table("{$this->stagingDbName}.{$tblName}")->count();
                    $prodCount = (int) DB::table("{$this->prodDbName}.{$tblName}")->count();
                    if ($stagingCount > $prodCount) {
                        $toInsert = $stagingCount - $prodCount;
                        $unchanged = $prodCount;
                    } else {
                        $unchanged = $stagingCount;
                    }
                }

                $tablesSummary[$tblName] = [
                    'table_name' => $tblName,
                    'is_new_table' => false,
                    'new_columns_count' => count($newColsForTable),
                    'new_columns' => $newColsForTable,
                    'records_to_insert' => $toInsert,
                    'records_to_update' => $toUpdate,
                    'records_unchanged' => $unchanged,
                    'primary_key' => $pkCols,
                    'common_columns' => $commonCols,
                ];

                $totalRecordsToInsert += $toInsert;
                $totalRecordsToUpdate += $toUpdate;
                $totalRecordsUnchanged += $unchanged;
            }
        }

        return [
            'tables_to_create' => $tablesToCreate,
            'columns_to_add' => $columnsToAdd,
            'tables_summary' => $tablesSummary,
            'total_records_to_insert' => $totalRecordsToInsert,
            'total_records_to_update' => $totalRecordsToUpdate,
            'total_records_unchanged' => $totalRecordsUnchanged,
            'warnings' => $warnings,
        ];
    }

    /**
     * Create isolated staging database.
     */
    public function createStagingDatabase(): void
    {
        DB::statement("CREATE DATABASE IF NOT EXISTS `{$this->stagingDbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    }

    /**
     * Drop staging database.
     */
    public function dropStagingDatabase(): void
    {
        DB::statement("DROP DATABASE IF EXISTS `{$this->stagingDbName}`");
    }

    /**
     * Load an SQL file into the staging database.
     */
    protected function loadSqlIntoStaging(string $sqlFilePath): void
    {
        $dbConfig = config('database.connections.mysql');
        $dbUser = $dbConfig['username'] ?? 'root';
        $dbPass = $dbConfig['password'] ?? '';
        $dbHost = $dbConfig['host'] ?? '127.0.0.1';
        $dbPort = $dbConfig['port'] ?? '3306';

        $tempCnf = tempnam(sys_get_temp_dir(), 'my_stg_');
        $cnfContent = "[client]\nuser=\"{$dbUser}\"\npassword=\"{$dbPass}\"\nhost=\"{$dbHost}\"\nport=\"{$dbPort}\"\n";
        file_put_contents($tempCnf, $cnfContent);
        chmod($tempCnf, 0600);

        try {
            $cmd = sprintf(
                'mysql --defaults-extra-file=%s --default-character-set=utf8mb4 %s < %s',
                escapeshellarg($tempCnf),
                escapeshellarg($this->stagingDbName),
                escapeshellarg($sqlFilePath)
            );

            $output = [];
            $exitCode = 0;
            exec($cmd . ' 2>&1', $output, $exitCode);

            if ($exitCode !== 0) {
                $err = implode("\n", $output);
                throw new \Exception("Failed to load uploaded SQL into staging: {$err}");
            }
        } finally {
            if (file_exists($tempCnf)) {
                @unlink($tempCnf);
            }
        }
    }

    /**
     * Ensure the staging database is populated (reloads if needed).
     */
    protected function ensureStagingPopulated(string $sqlFilePath): void
    {
        $stagingTables = $this->getTablesList($this->stagingDbName);
        if (empty($stagingTables)) {
            $this->createStagingDatabase();
            $this->loadSqlIntoStaging($sqlFilePath);
        }
    }

    /**
     * List all table names in a specific schema.
     */
    protected function getTablesList(string $schema): array
    {
        $rows = DB::select("
            SELECT TABLE_NAME as name 
            FROM information_schema.TABLES 
            WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = 'BASE TABLE'
            ORDER BY TABLE_NAME
        ", [$schema]);

        return array_map(function ($r) {
            $arr = array_change_key_case((array) $r, CASE_LOWER);
            return (string) ($arr['name'] ?? '');
        }, $rows);
    }

    /**
     * Get detailed column metadata for a table in a schema.
     */
    protected function getColumnsDetails(string $schema, string $table): array
    {
        $rows = DB::select("
            SELECT 
                COLUMN_NAME as name,
                COLUMN_TYPE as type,
                IS_NULLABLE as is_nullable,
                COLUMN_DEFAULT as default_val,
                EXTRA as extra,
                COLUMN_COMMENT as comment
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
            ORDER BY ORDINAL_POSITION
        ", [$schema, $table]);

        $columns = [];
        foreach ($rows as $r) {
            $arr = array_change_key_case((array) $r, CASE_LOWER);
            $name = (string) ($arr['name'] ?? '');
            $columns[$name] = [
                'name' => $name,
                'type' => (string) ($arr['type'] ?? 'varchar(255)'),
                'is_nullable' => ($arr['is_nullable'] ?? 'YES') === 'YES',
                'default' => $arr['default_val'] ?? null,
                'extra' => (string) ($arr['extra'] ?? ''),
                'comment' => (string) ($arr['comment'] ?? ''),
            ];
        }

        return $columns;
    }

    /**
     * Build an ALTER TABLE column definition string from column metadata.
     */
    protected function buildColumnDefinition(array $colData): string
    {
        $sql = $colData['type'];

        if (!$colData['is_nullable']) {
            $sql .= " NOT NULL";
        } else {
            $sql .= " NULL";
        }

        if ($colData['default'] !== null) {
            $def = $colData['default'];
            if (in_array(strtoupper($def), ['CURRENT_TIMESTAMP', 'CURRENT_TIMESTAMP()', 'NOW()'])) {
                $sql .= " DEFAULT CURRENT_TIMESTAMP";
            } else {
                $sql .= " DEFAULT " . DB::getPdo()->quote($def);
            }
        } elseif ($colData['is_nullable']) {
            $sql .= " DEFAULT NULL";
        }

        if (!empty($colData['extra']) && !str_contains(strtolower($colData['extra']), 'auto_increment')) {
            $sql .= " " . $colData['extra'];
        }

        if (!empty($colData['comment'])) {
            $sql .= " COMMENT " . DB::getPdo()->quote($colData['comment']);
        }

        return $sql;
    }

    /**
     * Get Primary Key column names for a table.
     */
    protected function getPrimaryKeyColumns(string $schema, string $table): array
    {
        $rows = DB::select("
            SELECT COLUMN_NAME as name
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = 'PRIMARY'
            ORDER BY ORDINAL_POSITION
        ", [$schema, $table]);

        return array_map(function ($r) {
            $arr = array_change_key_case((array) $r, CASE_LOWER);
            return (string) ($arr['name'] ?? '');
        }, $rows);
    }
}

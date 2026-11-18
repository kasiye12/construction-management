<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DatabaseSyncLog extends Model
{
    use HasFactory;

    protected $table = 'database_sync_logs';

    protected $fillable = [
        'batch_id',
        'uploaded_filename',
        'file_path',
        'file_size',
        'user_id',
        'admin_name',
        'status',
        'tables_created_count',
        'columns_added_count',
        'records_inserted_count',
        'records_updated_count',
        'records_unchanged_count',
        'backup_file',
        'tables_created',
        'columns_added',
        'changes_summary',
        'incompatible_warnings',
        'error_message',
        'applied_at',
        'rolled_back_at',
    ];

    protected $casts = [
        'tables_created' => 'array',
        'columns_added' => 'array',
        'changes_summary' => 'array',
        'incompatible_warnings' => 'array',
        'applied_at' => 'datetime',
        'rolled_back_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isApplied(): bool
    {
        return $this->status === 'applied';
    }

    public function isRolledBack(): bool
    {
        return $this->status === 'rolled_back';
    }

    public function canRollback(): bool
    {
        return $this->status === 'applied' && !empty($this->backup_file) && file_exists($this->backup_file);
    }

    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = (int) $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}

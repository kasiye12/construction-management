<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'name', 'file_path', 'file_type', 'file_size',
        'documentable_type', 'documentable_id', 'uploaded_by', 'description'
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    public function documentable()
    {
        return $this->morphTo();
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getFileSizeFormattedAttribute(): string
    {
        if (!$this->file_size) return 'N/A';
        $bytes = $this->file_size;
        if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' KB';
        return $bytes . ' bytes';
    }

    public function getFileIconAttribute(): string
    {
        return match(strtolower($this->file_type ?? '')) {
            'pdf' => 'fa-file-pdf',
            'jpg', 'jpeg', 'png', 'gif', 'image' => 'fa-file-image',
            'doc', 'docx' => 'fa-file-word',
            'xls', 'xlsx' => 'fa-file-excel',
            default => 'fa-file',
        };
    }
}

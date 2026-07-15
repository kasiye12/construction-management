<?php
namespace App\Traits;

use App\Models\Document;

trait HasDocuments
{
    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }
}

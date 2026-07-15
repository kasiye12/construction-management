<div class="table-card mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">📎 Documents</h5>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#uploadModal{{ $model->id ?? '' }}">
            <i class="fas fa-upload me-1"></i> Upload
        </button>
    </div>
    
    @if(isset($model) && $model->documents && $model->documents->count() > 0)
    <div class="table-responsive">
        <table class="table table-sm">
            <thead>
                <tr><th>File</th><th>Type</th><th>Size</th><th>Uploaded</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @foreach($model->documents as $doc)
                <tr>
                    <td>
                        <i class="fas fa-file me-2"></i>
                        {{ $doc->name }}
                    </td>
                    <td><span class="badge bg-secondary">{{ strtoupper($doc->file_type) }}</span></td>
                    <td>{{ $doc->file_size_formatted ?? 'N/A' }}</td>
                    <td><small>{{ $doc->created_at->diffForHumans() }}</small></td>
                    <td>
                        <a href="{{ route('documents.download', $doc) }}" class="btn btn-sm btn-success" title="Download">
                            <i class="fas fa-download"></i>
                        </a>
                        <form action="{{ route('documents.destroy', $doc) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this file?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <p class="text-muted">No documents uploaded yet.</p>
    @endif
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal{{ $model->id ?? '' }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('documents.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="documentable_type" value="{{ $type ?? 'App\\Models\\Project' }}">
                <input type="hidden" name="documentable_id" value="{{ $model->id ?? '' }}">
                
                <div class="modal-header">
                    <h5 class="modal-title">Upload Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">File <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control" required>
                        <small class="text-muted">Max size: 10MB</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Document Name</label>
                        <input type="text" name="name" class="form-control" placeholder="Leave blank to use file name">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

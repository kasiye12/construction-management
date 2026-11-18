@extends('layouts.app')

@section('title', 'Import Projects - Master Registry - CMS')

@section('content')
<div class="page-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2>📥 Import Projects (Master Registry)</h2>
            <p class="text-muted mb-0">Bulk import projects from Excel (<code>.xlsx</code>, <code>.xls</code>) or CSV (<code>.csv</code>) files.</p>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 mt-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('projects.index') }}">Projects</a></li>
                    <li class="breadcrumb-item active">Import</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('projects.template.download', ['format' => 'xlsx']) }}" class="btn btn-outline-success">
                <i class="fas fa-file-excel me-1"></i> Download Excel Template
            </a>
            <a href="{{ route('projects.template.download', ['format' => 'csv']) }}" class="btn btn-outline-secondary">
                <i class="fas fa-file-csv me-1"></i> Download CSV Template
            </a>
            <a href="{{ route('projects.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-1"></i> Back to Projects
            </a>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Upload Form Card -->
    <div class="col-lg-7">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-primary text-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="fas fa-cloud-upload-alt me-2"></i> Upload Project Data File
                    </h5>
                    <span class="badge bg-light text-primary fw-bold">XLSX / XLS / CSV</span>
                </div>
            </div>
            <div class="card-body p-4">
                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                <form action="{{ route('projects.import.post') }}" method="POST" enctype="multipart/form-data" id="importProjectForm">
                    @csrf

                    <div class="mb-4">
                        <label for="projectFile" class="form-label fw-bold">Choose Spreadsheet or CSV File <span class="text-danger">*</span></label>
                        <div class="border border-2 border-dashed rounded-3 p-4 text-center bg-light" id="dropArea" style="border-color: #cbd5e1 !important; cursor: pointer;">
                            <i class="fas fa-file-excel text-success fa-3x mb-3"></i>
                            <h6 class="fw-bold mb-1">Click to browse or drag & drop file here</h6>
                            <p class="text-muted small mb-3">Supported formats: Microsoft Excel (<code>.xlsx</code>, <code>.xls</code>), Comma Separated (<code>.csv</code>) up to 30MB</p>
                            <input type="file" name="file" id="projectFile" class="form-control d-none @error('file') is-invalid @enderror" accept=".xlsx,.xls,.csv,.txt" required>
                            <button type="button" class="btn btn-primary btn-sm px-4" onclick="document.getElementById('projectFile').click()">
                                <i class="fas fa-folder-open me-1"></i> Select File
                            </button>
                            <div id="fileSelectedName" class="mt-2 fw-bold text-success" style="display:none;"></div>
                        </div>
                        @error('file')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="card bg-light border-0 mb-4">
                        <div class="card-body py-3">
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" name="update_existing" id="updateExisting" value="1" checked>
                                <label class="form-check-label fw-bold" for="updateExisting">
                                    Update existing projects if project name matches
                                </label>
                                <div class="text-muted small">
                                    When checked, existing projects with matching names will have their location, client, consultant, contract amount, and dates refreshed.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('projects.index') }}" class="btn btn-secondary">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-success px-4 py-2" id="submitBtn">
                            <i class="fas fa-file-import me-1"></i> Start Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Instructions & Format Guide -->
    <div class="col-lg-5">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-dark text-white py-3">
                <h6 class="mb-0 fw-bold">
                    <i class="fas fa-table me-2"></i> Accepted Column Format
                </h6>
            </div>
            <div class="card-body p-3">
                <p class="small text-muted mb-3">
                    The importer automatically scans the top rows and detects the table header. The columns below are supported:
                </p>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0" style="font-size: 0.85rem;">
                        <thead class="table-light">
                            <tr>
                                <th>Header Column</th>
                                <th>Required</th>
                                <th>Sample Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>No</code> / <code>Code</code></td>
                                <td><span class="badge bg-secondary">Optional</span></td>
                                <td>1, 2, PRJ-001</td>
                            </tr>
                            <tr>
                                <td><code>Project Name</code></td>
                                <td><span class="badge bg-danger">Required</span></td>
                                <td>Bole Lemhi Phase - I...</td>
                            </tr>
                            <tr>
                                <td><code>Contract Amount</code></td>
                                <td><span class="badge bg-secondary">Optional</span></td>
                                <td>123,898,313.93 ETB</td>
                            </tr>
                            <tr>
                                <td><code>TNT Share (%)</code></td>
                                <td><span class="badge bg-secondary">Optional</span></td>
                                <td>1 (100%), 0.95 (95%), 0.33 (33%)</td>
                            </tr>
                            <tr>
                                <td><code>Contract Signing date</code></td>
                                <td><span class="badge bg-secondary">Optional</span></td>
                                <td>2018-02-05 / YYYY-MM-DD</td>
                            </tr>
                            <tr>
                                <td><code>Client</code></td>
                                <td><span class="badge bg-secondary">Optional</span></td>
                                <td>Industrial Parks Dev. Corp.</td>
                            </tr>
                            <tr>
                                <td><code>Consultant</code></td>
                                <td><span class="badge bg-secondary">Optional</span></td>
                                <td>EEC - Water & Energy Sector</td>
                            </tr>
                            <tr>
                                <td><code>Project Location</code></td>
                                <td><span class="badge bg-secondary">Optional</span></td>
                                <td>Addis Ababa, Ambo, Hawassa</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 border-start border-4 border-info">
            <div class="card-body p-3">
                <h6 class="fw-bold text-info mb-2"><i class="fas fa-lightbulb me-1"></i> Smart Import Engine</h6>
                <ul class="small text-muted ps-3 mb-0">
                    <li class="mb-1"><strong>Auto-Header Detection:</strong> Works with TNT title banners and header rows positioned anywhere in the first few rows.</li>
                    <li class="mb-1"><strong>Cleans Summaries:</strong> Automatically skips footer rows such as <em>"Total Contract Value"</em> or blank spacer rows.</li>
                    <li class="mb-1"><strong>Flexible Numbers:</strong> Currency symbols, spaces, and commas in contract amounts are parsed automatically.</li>
                    <li><strong>Share Normalization:</strong> Values like <code>1</code>, <code>0.95</code>, <code>33%</code>, or <code>100</code> are properly interpreted as percentages.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('projectFile');
    const dropArea = document.getElementById('dropArea');
    const fileNameDisplay = document.getElementById('fileSelectedName');
    const form = document.getElementById('importProjectForm');
    const submitBtn = document.getElementById('submitBtn');

    if (fileInput) {
        fileInput.addEventListener('change', function() {
            if (fileInput.files.length > 0) {
                const name = fileInput.files[0].name;
                const size = (fileInput.files[0].size / 1024).toFixed(1);
                fileNameDisplay.innerHTML = `<i class="fas fa-check-circle me-1"></i> Selected: <strong>${name}</strong> (${size} KB)`;
                fileNameDisplay.style.display = 'block';
            }
        });

        // Drag and drop events
        ['dragenter', 'dragover'].forEach(eventName => {
            dropArea.addEventListener(eventName, (e) => {
                e.preventDefault();
                dropArea.classList.add('bg-white', 'border-primary');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, (e) => {
                e.preventDefault();
                dropArea.classList.remove('bg-white', 'border-primary');
            }, false);
        });

        dropArea.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files.length > 0) {
                fileInput.files = files;
                const name = files[0].name;
                const size = (files[0].size / 1024).toFixed(1);
                fileNameDisplay.innerHTML = `<i class="fas fa-check-circle me-1"></i> Selected: <strong>${name}</strong> (${size} KB)`;
                fileNameDisplay.style.display = 'block';
            }
        });
    }

    if (form) {
        form.addEventListener('submit', function() {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Importing Projects...';
        });
    }
});
</script>
@endsection

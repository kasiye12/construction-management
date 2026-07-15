<?php
namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function upload(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'documentable_type' => 'required|string',
            'documentable_id' => 'required|integer',
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        
        // Store file
        $path = $file->store('documents/' . date('Y/m'), 'public');
        
        // Create document record
        $document = Document::create([
            'name' => $validated['name'] ?? $originalName,
            'file_path' => $path,
            'file_type' => $extension,
            'file_size' => $file->getSize(),
            'documentable_type' => $validated['documentable_type'],
            'documentable_id' => $validated['documentable_id'],
            'uploaded_by' => Auth::id(),
            'description' => $validated['description'] ?? null,
        ]);

        return back()->with('success', 'File uploaded successfully.');
    }

    public function download(Document $document)
    {
        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'File not found.');
        }

        return Storage::disk('public')->download(
            $document->file_path,
            $document->name
        );
    }

    public function destroy(Document $document)
    {
        // Delete file from storage
        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }
        
        $document->delete();

        return back()->with('success', 'Document deleted.');
    }

    public function preview(Document $document)
    {
        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404);
        }

        return response()->file(Storage::disk('public')->path($document->file_path));
    }
}

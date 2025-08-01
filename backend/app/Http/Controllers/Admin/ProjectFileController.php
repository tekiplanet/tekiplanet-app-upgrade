<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProjectFileController extends Controller
{
    public function store(Request $request, Project $project)
    {
        try {
            $request->validate([
                'file' => 'required|file|max:10240' // 10MB max
            ]);

            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            
            // Generate a unique name for storage
            $uniqueName = uniqid() . '-' . $fileName;
            
            // Store the file in the public disk under project-files directory
            $path = $file->storePublicly('project-files', 'public');

            // Debug log
            \Log::info('File Upload:', [
                'original_name' => $fileName,
                'stored_path' => $path,
                'full_url' => Storage::disk('public')->url($path),
                'exists' => Storage::disk('public')->exists($path)
            ]);

            // Create file record
            $projectFile = $project->files()->create([
                'name' => $fileName,
                'file_path' => $path,
                'uploaded_by' => auth('admin')->id(),
                'file_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully',
                'data' => [
                    'file' => $projectFile,
                    'url' => Storage::disk('public')->url($path),
                    'exists' => Storage::disk('public')->exists($path),
                    'storage_path' => storage_path('app/public/' . $path)
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('File Upload Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload file: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Project $project, ProjectFile $file)
    {
        try {
            $file->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'File deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete file: ' . $e->getMessage()
            ], 500);
        }
    }
} 
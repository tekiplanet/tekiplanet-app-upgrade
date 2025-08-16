<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserFile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AdminFileManagementController extends Controller
{
    /**
     * Display the file management dashboard
     */
    public function index()
    {
        return view('admin.file-management.index');
    }

    /**
     * Get all files with filters
     */
    public function files(Request $request): JsonResponse
    {
        $query = UserFile::with(['sender', 'receiver', 'category']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('sender_id')) {
            $query->where('sender_id', $request->sender_id);
        }

        if ($request->filled('receiver_id')) {
            $query->where('receiver_id', $request->receiver_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('file_name', 'like', "%{$search}%")
                  ->orWhere('original_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 20);
        $files = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $files
        ]);
    }

    /**
     * Get file details
     */
    public function show(string $id): JsonResponse
    {
        $file = UserFile::with(['sender', 'receiver', 'category', 'permissions.user'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $file
        ]);
    }

    /**
     * Delete a file
     */
    public function destroy(string $id): JsonResponse
    {
        $file = UserFile::findOrFail($id);
        
        // TODO: Delete from Cloudinary as well
        $file->markAsDeleted();

        return response()->json([
            'success' => true,
            'message' => 'File deleted successfully'
        ]);
    }

    /**
     * Get file statistics
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total_files' => UserFile::count(),
            'active_files' => UserFile::where('status', 'active')->count(),
            'deleted_files' => UserFile::where('status', 'deleted')->count(),
            'expired_files' => UserFile::where('status', 'expired')->count(),
            'total_storage_used' => UserFile::where('status', 'active')->sum('file_size'),
            'total_downloads' => UserFile::sum('download_count'),
            'files_by_category' => UserFile::select('category_id', DB::raw('count(*) as count'))
                ->where('status', 'active')
                ->groupBy('category_id')
                ->with('category:id,name')
                ->get(),
            'files_by_status' => UserFile::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get(),
            'recent_uploads' => UserFile::with(['sender', 'category'])
                ->where('status', 'active')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get user storage usage
     */
    public function userStorageUsage(): JsonResponse
    {
        $usage = User::select('users.id', 'users.name', 'users.email', 'users.platform_id')
            ->selectRaw('COUNT(user_files.id) as file_count')
            ->selectRaw('SUM(user_files.file_size) as total_size')
            ->leftJoin('user_files', function ($join) {
                $join->on('users.id', '=', 'user_files.sender_id')
                     ->where('user_files.status', '=', 'active');
            })
            ->groupBy('users.id', 'users.name', 'users.email', 'users.platform_id')
            ->orderBy('total_size', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $usage
        ]);
    }

    /**
     * Get storage usage by date
     */
    public function storageUsageByDate(Request $request): JsonResponse
    {
        $days = $request->get('days', 30);
        
        $usage = UserFile::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as file_count'),
            DB::raw('SUM(file_size) as total_size')
        )
        ->where('status', 'active')
        ->where('created_at', '>=', now()->subDays($days))
        ->groupBy('date')
        ->orderBy('date')
        ->get();

        return response()->json([
            'success' => true,
            'data' => $usage
        ]);
    }

    /**
     * Get popular file types
     */
    public function popularFileTypes(): JsonResponse
    {
        $types = UserFile::select('file_extension', DB::raw('count(*) as count'))
            ->where('status', 'active')
            ->groupBy('file_extension')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $types
        ]);
    }

    /**
     * Get system health indicators
     */
    public function systemHealth(): JsonResponse
    {
        $health = [
            'total_files' => UserFile::count(),
            'active_files' => UserFile::where('status', 'active')->count(),
            'expired_files' => UserFile::where('status', 'expired')->count(),
            'total_storage_mb' => round(UserFile::where('status', 'active')->sum('file_size') / 1024 / 1024, 2),
            'average_file_size_mb' => round(UserFile::where('status', 'active')->avg('file_size') / 1024 / 1024, 2),
            'files_uploaded_today' => UserFile::whereDate('created_at', today())->count(),
            'files_uploaded_this_week' => UserFile::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'files_uploaded_this_month' => UserFile::whereMonth('created_at', now()->month)->count(),
            'total_downloads' => UserFile::sum('download_count'),
            'downloads_today' => UserFile::whereDate('updated_at', today())->sum('download_count')
        ];

        return response()->json([
            'success' => true,
            'data' => $health
        ]);
    }

    /**
     * Bulk delete files
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $validator = validator($request->all(), [
            'file_ids' => 'required|array',
            'file_ids.*' => 'uuid|exists:user_files,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $deletedCount = UserFile::whereIn('id', $request->file_ids)
            ->update(['status' => 'deleted']);

        return response()->json([
            'success' => true,
            'message' => "{$deletedCount} files deleted successfully"
        ]);
    }

    /**
     * Clean up expired files
     */
    public function cleanupExpired(): JsonResponse
    {
        $expiredFiles = UserFile::where('expires_at', '<', now())
            ->where('status', 'active')
            ->get();

        $count = 0;
        foreach ($expiredFiles as $file) {
            $file->markAsExpired();
            $count++;
        }

        return response()->json([
            'success' => true,
            'message' => "{$count} expired files cleaned up successfully"
        ]);
    }
}

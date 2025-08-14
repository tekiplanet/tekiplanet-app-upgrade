<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Grit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GritController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Grit::with(['category', 'user', 'applications'])
                ->when($request->search, function ($query, $search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                })
                ->when($request->status, function ($query, $status) {
                    $query->where('status', $status);
                })
                ->when($request->category, function ($query, $category) {
                    $query->where('category_id', $category);
                });

            // Filter by hustle type (admin created vs business created)
            if ($request->has('type')) {
                if ($request->type === 'admin_created') {
                    $query->whereNull('created_by_user_id');
                } elseif ($request->type === 'business_created') {
                    $query->whereNotNull('created_by_user_id');
                }
            }

            // Filter by approval status for business-created GRITs
            if ($request->has('approval_status') && ($request->type === 'business_created' || !$request->type)) {
                $query->where('admin_approval_status', $request->approval_status);
            }

            $grits = $query->latest()->paginate(10);
            $categories = \App\Models\ProfessionalCategory::all();

            // Get pending GRITs count for the badge
            $pendingGritsCount = Grit::whereNotNull('created_by_user_id')
                ->where('admin_approval_status', 'pending')
                ->count();

            return view('admin.grits.index', compact('grits', 'categories', 'pendingGritsCount'));

        } catch (\Exception $e) {
            Log::error('Error fetching grits for admin:', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Failed to fetch GRITs']);
        }
    }

    public function updateApprovalStatus(Request $request, Grit $grit)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|string|in:approved,rejected',
                'reason' => 'nullable|string|max:500'
            ]);

            $oldStatus = $grit->admin_approval_status;
            
            if ($validated['status'] === 'approved') {
                $grit->update([
                    'admin_approval_status' => 'approved',
                    'status' => 'open' // Make it visible to professionals
                ]);

                // Send approval notification to business owner
                $notificationService = app(\App\Services\NotificationService::class);
                $notificationService->send([
                    'type' => 'grit_approved',
                    'title' => 'GRIT Approved',
                    'message' => "Your GRIT '{$grit->title}' has been approved and is now visible to professionals.",
                    'icon' => 'check-circle',
                    'action_url' => '/dashboard/grits/mine',
                    'extra_data' => [
                        'grit_id' => $grit->id,
                        'category_id' => $grit->category_id,
                    ]
                ], $grit->user);

                $message = 'GRIT approved successfully. It is now visible to professionals.';
            } else {
                $grit->update([
                    'admin_approval_status' => 'rejected',
                    'status' => 'cancelled'
                ]);

                // Send rejection notification to business owner
                $notificationService = app(\App\Services\NotificationService::class);
                $notificationService->send([
                    'type' => 'grit_rejected',
                    'title' => 'GRIT Rejected',
                    'message' => "Your GRIT '{$grit->title}' has been rejected." . 
                                ($validated['reason'] ? " Reason: {$validated['reason']}" : ""),
                    'icon' => 'x-circle',
                    'action_url' => '/dashboard/grits/mine',
                    'extra_data' => [
                        'grit_id' => $grit->id,
                        'category_id' => $grit->category_id,
                    ]
                ], $grit->user);

                $message = 'GRIT rejected successfully.';
            }

            // Log the status change
            Log::info('GRIT approval status updated', [
                'grit_id' => $grit->id,
                'old_status' => $oldStatus,
                'new_status' => $validated['status'],
                'updated_by' => auth()->id(),
                'reason' => $validated['reason'] ?? null
            ]);

            return response()->json([
                'success' => true,
                'message' => $message,
                'grit' => $grit->fresh(['category', 'user'])
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error updating grit approval status:', [
                'grit_id' => $grit->id, 
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['message' => 'Failed to update grit status'], 500);
        }
    }

    /**
     * Get count of pending GRITs for admin dashboard
     */
    public function getPendingCount()
    {
        try {
            $pendingCount = Grit::where('admin_approval_status', 'pending')->count();
            
            return response()->json([
                'success' => true,
                'pending_count' => $pendingCount
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching pending GRITs count:', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to fetch pending count'], 500);
        }
    }

    /**
     * Get GRITs by approval status with detailed filtering
     */
    public function getByStatus(Request $request)
    {
        try {
            $query = Grit::with(['category', 'user', 'applications'])
                ->latest();

            // Filter by approval status
            if ($request->has('approval_status')) {
                $query->where('admin_approval_status', $request->approval_status);
            }

            // Filter by category
            if ($request->has('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            // Search functionality
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }

            $grits = $query->paginate(15);

            return response()->json([
                'success' => true,
                'grits' => $grits
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching GRITs by status:', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to fetch GRITs'], 500);
        }
    }

    /**
     * Show the form for creating a new GRIT
     */
    public function create()
    {
        $categories = \App\Models\ProfessionalCategory::all();
        return view('admin.grits.create', compact('categories'));
    }

    /**
     * Store a newly created GRIT
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'category_id' => 'required|exists:professional_categories,id',
                'budget' => 'required|numeric|min:0',
                'deadline' => 'required|date|after:today',
                'requirements' => 'nullable|string',
            ]);

            $grit = Grit::create([
                ...$validated,
                'admin_approval_status' => 'approved', // Admin-created GRITs are auto-approved
                'status' => 'open'
            ]);

            return redirect()->route('admin.grits.show', $grit)
                ->with('success', 'GRIT created successfully.');

        } catch (\Exception $e) {
            Log::error('Failed to create GRIT: ' . $e->getMessage());
            return back()->withInput()->withErrors(['error' => 'Failed to create GRIT: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified GRIT
     */
    public function show(Grit $grit)
    {
        $grit->load([
            'category',
            'user',
            'applications.professional.user',
            'applications.professional.category'
        ]);
        
        return view('admin.grits.show', compact('grit'));
    }

    /**
     * Show the form for editing the specified GRIT
     */
    public function edit(Grit $grit)
    {
        $categories = \App\Models\ProfessionalCategory::all();
        return view('admin.grits.edit', compact('grit', 'categories'));
    }

    /**
     * Update the specified GRIT
     */
    public function update(Request $request, Grit $grit)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'category_id' => 'required|exists:professional_categories,id',
                'budget' => 'required|numeric|min:0',
                'deadline' => 'required|date',
                'requirements' => 'nullable|string',
            ]);

            $grit->update($validated);

            return redirect()->route('admin.grits.show', $grit)
                ->with('success', 'GRIT updated successfully.');

        } catch (\Exception $e) {
            Log::error('Failed to update GRIT: ' . $e->getMessage());
            return back()->withInput()->withErrors(['error' => 'Failed to update GRIT: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified GRIT
     */
    public function destroy(Grit $grit)
    {
        try {
            $grit->delete();
            return redirect()->route('admin.grits.index')
                ->with('success', 'GRIT deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to delete GRIT: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to delete GRIT: ' . $e->getMessage()]);
        }
    }
}

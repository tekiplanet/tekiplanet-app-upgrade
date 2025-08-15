<?php

namespace App\Http\Controllers;

use App\Models\Grit;
use App\Models\GritApplication;
use App\Models\Professional;
use App\Models\User;
use App\Services\NotificationService;
use App\Jobs\SendGritApplicationNotification;
use App\Jobs\SendGritApplicationStatusNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewGritApplicationSubmitted;
use App\Notifications\NewGritApplicationNotification;

class GritApplicationController extends Controller
{
    /**
     * Display a listing of applications for a specific GRIT
     */
    public function index(Request $request, string $gritId)
    {
        try {
            $grit = Grit::findOrFail($gritId);
            
            // Check if user has permission to view applications
            // Admin can view all, business owner can view their own GRITs
            if (Auth::user()->role !== 'admin' && $grit->created_by_user_id !== Auth::id()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $applications = GritApplication::with(['professional.user', 'professional.category'])
                ->where('grit_id', $gritId)
                ->latest()
                ->paginate($request->get('per_page', 10))
                ->through(function($application) {
                    return [
                        'id' => $application->id,
                        'professional' => [
                            'id' => $application->professional->id,
                            'name' => $application->professional->user->name,
                            'email' => $application->professional->user->email,
                            'category' => $application->professional->category->name ?? 'No Category',
                            'completion_rate' => $application->professional->completion_rate ?? 0,
                            'average_rating' => $application->professional->average_rating ?? 0,
                            'total_projects_completed' => $application->professional->total_projects_completed ?? 0,
                            'qualifications' => $application->professional->qualifications,
                        ],
                        'status' => $application->status,
                        'applied_at' => $application->created_at->format('M d, Y'),
                        'created_at' => $application->created_at->toISOString(),
                    ];
                });

            return response()->json([
                'applications' => $applications->items(),
                'pagination' => [
                    'current_page' => $applications->currentPage(),
                    'last_page' => $applications->lastPage(),
                    'per_page' => $applications->perPage(),
                    'total' => $applications->total(),
                    'from' => $applications->firstItem(),
                    'to' => $applications->lastItem(),
                ],
                'grit' => [
                    'id' => $grit->id,
                    'title' => $grit->title,
                    'status' => $grit->status,
                    'admin_approval_status' => $grit->admin_approval_status,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching GRIT applications:', [
                'grit_id' => $gritId,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['message' => 'Failed to fetch applications'], 500);
        }
    }

    /**
     * Display the specified application
     */
    public function show(string $applicationId)
    {
        try {
            $application = GritApplication::with(['professional.user', 'professional.category', 'grit'])
                ->findOrFail($applicationId);

            // Check if user has permission to view this application
            if (Auth::user()->role !== 'admin' && $application->grit->created_by_user_id !== Auth::id()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            return response()->json([
                'application' => [
                    'id' => $application->id,
                    'professional' => [
                        'id' => $application->professional->id,
                        'name' => $application->professional->user->name,
                        'email' => $application->professional->user->email,
                        'category' => $application->professional->category->name ?? 'No Category',
                        'completion_rate' => $application->professional->completion_rate ?? 0,
                        'average_rating' => $application->professional->average_rating ?? 0,
                        'total_projects_completed' => $application->professional->total_projects_completed ?? 0,
                        'qualifications' => $application->professional->qualifications,
                        'portfolio_items' => $application->professional->portfolio_items ?? [],
                    ],
                    'grit' => [
                        'id' => $application->grit->id,
                        'title' => $application->grit->title,
                        'status' => $application->grit->status,
                    ],
                    'status' => $application->status,
                    'applied_at' => $application->created_at->format('M d, Y'),
                    'created_at' => $application->created_at->toISOString(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching GRIT application:', [
                'application_id' => $applicationId,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['message' => 'Failed to fetch application'], 500);
        }
    }

    /**
     * Update the status of an application
     */
    public function updateStatus(Request $request, string $applicationId)
    {
        try {
            $request->validate([
                'status' => 'required|in:approved,rejected,withdrawn'
            ]);

            $application = GritApplication::with(['grit', 'professional.user'])
                ->findOrFail($applicationId);

            // Check if user has permission to update this application
            if (Auth::user()->role !== 'admin' && $application->grit->created_by_user_id !== Auth::id()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            // Check if application can be updated (only pending applications can be approved/rejected)
            if ($application->status !== 'pending' && in_array($request->status, ['approved', 'rejected'])) {
                return response()->json(['message' => 'Application status cannot be changed'], 400);
            }

            $oldStatus = $application->status;
            $application->status = $request->status;
            $application->save();

            // If approved, assign the professional to the GRIT and reject other applications
            if ($request->status === 'approved' && $application->grit->status === 'open') {
                $application->grit->update([
                    'assigned_professional_id' => $application->professional_id,
                    'status' => 'in_progress'
                ]);

                // Reject all other pending applications for this GRIT
                $otherApplications = GritApplication::where('grit_id', $application->grit_id)
                    ->where('id', '!=', $application->id)
                    ->where('status', 'pending')
                    ->get();

                foreach ($otherApplications as $otherApplication) {
                    $otherApplication->update(['status' => 'rejected']);
                    
                    // Send notification for auto-rejected applications
                    dispatch(new SendGritApplicationStatusNotification(
                        $otherApplication, 
                        'rejected', 
                        'Another professional has been assigned to this GRIT'
                    ));
                }
            }

            // Send notification for the main action (approved or rejected)
            dispatch(new SendGritApplicationStatusNotification(
                $application, 
                $request->status
            ));

            return response()->json([
                'message' => 'Application status updated successfully',
                'application' => [
                    'id' => $application->id,
                    'status' => $application->status,
                    'updated_at' => $application->updated_at->toISOString(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating GRIT application status:', [
                'application_id' => $applicationId,
                'status' => $request->status ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
            return response()->json(['message' => 'Failed to update application status'], 500);
        }
    }

    public function store(Request $request, string $gritId)
    {
        try {
            $professional = Professional::where('user_id', Auth::id())->first();
            if (!$professional) {
                return response()->json(['message' => 'Professional profile not found'], 422);
            }

            $grit = Grit::with(['user', 'category'])
                ->findOrFail($gritId);

            // Basic guards similar to frontend checks
            if ($grit->status !== 'open' || $grit->assigned_professional_id) {
                return response()->json(['message' => 'This GRIT is not accepting applications'], 422);
            }

            // Optional: require matching category
            if ($professional->category_id && $grit->category_id && $professional->category_id !== $grit->category_id) {
                return response()->json(['message' => 'This GRIT is for a different professional category'], 422);
            }

            // Prevent duplicates
            $existing = GritApplication::where('grit_id', $grit->id)
                ->where('professional_id', $professional->id)
                ->whereIn('status', ['pending', 'approved'])
                ->first();
            if ($existing) {
                return response()->json(['message' => 'You have already applied for this GRIT'], 422);
            }

            $application = GritApplication::create([
                'grit_id' => $grit->id,
                'professional_id' => $professional->id,
                'status' => 'pending',
            ]);

            // Queue the consolidated notification job (email + in-app/push)
            dispatch(new SendGritApplicationNotification($grit, $professional))
                ->onQueue('default');

            return response()->json([
                'message' => 'Application submitted successfully',
                'application' => [
                    'id' => $application->id,
                    'status' => $application->status,
                    'applied_at' => $application->created_at?->toISOString(),
                ],
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error submitting GRIT application', [
                'grit_id' => $gritId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            return response()->json(['message' => 'Failed to submit application'], 500);
        }
    }
}



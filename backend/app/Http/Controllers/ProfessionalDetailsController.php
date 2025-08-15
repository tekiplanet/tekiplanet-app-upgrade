<?php

namespace App\Http\Controllers;

use App\Models\Professional;
use App\Models\GritApplication;
use App\Models\Grit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Jobs\SendGritApplicationStatusNotification;

class ProfessionalDetailsController extends Controller
{
    /**
     * Get complete professional details for viewing
     */
    public function show($professionalId)
    {
        try {
            $professional = Professional::with([
                'user',
                'category',
                'gritApplications.grit',
                'assignedGrits'
            ])->findOrFail($professionalId);

            // Get professional's application for the specific GRIT (if provided)
            $gritId = request()->get('grit_id');
            $currentApplication = null;
            if ($gritId) {
                $currentApplication = GritApplication::where('grit_id', $gritId)
                    ->where('professional_id', $professionalId)
                    ->first();
            }

            // Calculate statistics
            $totalGritsCompleted = $professional->assignedGrits()
                ->where('status', 'completed')
                ->count();

            $totalEarnings = $professional->assignedGrits()
                ->where('status', 'completed')
                ->sum('professional_budget');

            $averageRating = $professional->average_rating ?? 0;
            $completionRate = $professional->completion_rate ?? 0;

            // Dummy reviews data (replace with actual reviews when implemented)
            $reviews = [
                [
                    'id' => 1,
                    'business_name' => 'TechCorp Solutions',
                    'grit_title' => 'E-commerce Website Development',
                    'rating' => 5,
                    'comment' => 'Excellent work! The professional delivered exactly what we needed on time and within budget. Highly recommended.',
                    'created_at' => '2024-01-15',
                    'project_amount' => 2500,
                    'currency' => 'USD'
                ],
                [
                    'id' => 2,
                    'business_name' => 'StartupXYZ',
                    'grit_title' => 'Mobile App Development',
                    'rating' => 4,
                    'comment' => 'Great communication and technical skills. The app was delivered as promised with good quality.',
                    'created_at' => '2024-01-10',
                    'project_amount' => 1800,
                    'currency' => 'USD'
                ],
                [
                    'id' => 3,
                    'business_name' => 'Digital Innovations Ltd',
                    'grit_title' => 'API Integration Project',
                    'rating' => 5,
                    'comment' => 'Outstanding performance! The professional went above and beyond our expectations. Will definitely work together again.',
                    'created_at' => '2024-01-05',
                    'project_amount' => 3200,
                    'currency' => 'USD'
                ]
            ];

            // Recent projects
            $recentProjects = $professional->assignedGrits()
                ->with(['user', 'category'])
                ->latest()
                ->take(5)
                ->get()
                ->map(function($grit) {
                    return [
                        'id' => $grit->id,
                        'title' => $grit->title,
                        'business_name' => $grit->user->name,
                        'category' => $grit->category->name,
                        'budget' => $grit->professional_budget,
                        'currency' => $grit->professional_currency,
                        'status' => $grit->status,
                        'completed_at' => $grit->updated_at,
                        'rating' => rand(4, 5), // Dummy rating
                        'review' => 'Great work and professional communication throughout the project.' // Dummy review
                    ];
                });

            return response()->json([
                'professional' => [
                    'id' => $professional->id,
                    'user' => [
                        'id' => $professional->user->id,
                        'name' => $professional->user->name,
                        'email' => $professional->user->email,
                        'phone' => $professional->user->phone,
                        'avatar' => $professional->user->avatar,
                        'created_at' => $professional->user->created_at->format('M d, Y')
                    ],
                    'category' => [
                        'id' => $professional->category->id,
                        'name' => $professional->category->name
                    ],
                    'title' => $professional->title,
                    'bio' => $professional->bio,
                    'experience_years' => $professional->years_of_experience,
                    'hourly_rate' => $professional->hourly_rate,
                    'completion_rate' => $completionRate,
                    'average_rating' => $averageRating,
                    'total_projects_completed' => $professional->total_projects_completed ?? 0,
                    'qualifications' => $professional->qualifications,
                    'expertise_areas' => $professional->expertise_areas ?? [],
                    'certifications' => $professional->certifications ?? [],
                    'portfolio_items' => $professional->portfolio_items ?? [],
                    'status' => $professional->status,
                    'verified_at' => $professional->verified_at,
                    'created_at' => $professional->created_at->format('M d, Y')
                ],
                'statistics' => [
                    'total_grits_completed' => $totalGritsCompleted,
                    'total_earnings' => $totalEarnings,
                    'average_rating' => $averageRating,
                    'completion_rate' => $completionRate,
                    'total_applications' => $professional->gritApplications->count(),
                    'active_projects' => $professional->assignedGrits()->where('status', 'in_progress')->count()
                ],
                'reviews' => $reviews,
                'recent_projects' => $recentProjects,
                'current_application' => $currentApplication ? [
                    'id' => $currentApplication->id,
                    'status' => $currentApplication->status,
                    'applied_at' => $currentApplication->created_at->format('M d, Y'),
                    'grit' => [
                        'id' => $currentApplication->grit->id,
                        'title' => $currentApplication->grit->title
                    ]
                ] : null
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching professional details:', [
                'professional_id' => $professionalId,
                'error' => $e->getMessage()
            ]);
            return response()->json(['message' => 'Failed to fetch professional details'], 500);
        }
    }

    /**
     * Update application status from professional details page
     */
    public function updateApplicationStatus(Request $request, $applicationId)
    {
        try {
            $request->validate([
                'status' => 'required|in:approved,rejected'
            ]);

            $application = GritApplication::with(['grit', 'professional.user'])
                ->findOrFail($applicationId);

            // Check if user has permission to update this application
            if (Auth::user()->role !== 'admin' && $application->grit->created_by_user_id !== Auth::id()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            // Check if application can be updated (only pending applications can be approved/rejected)
            if ($application->status !== 'pending') {
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
            Log::error('Error updating application status:', [
                'application_id' => $applicationId,
                'status' => $request->status ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
            return response()->json(['message' => 'Failed to update application status'], 500);
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hustle;
use App\Models\HustleApplication;
use Illuminate\Http\Request;
use App\Mail\ApplicationApproved;
use App\Mail\ApplicationRejected;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Mail;

class HustleApplicationController extends Controller
{
    public function index(Hustle $hustle)
    {
        $applications = $hustle->applications()
            ->with('professional')
            ->latest()
            ->paginate(10);

        return view('admin.hustles.applications.index', compact('hustle', 'applications'));
    }

    public function show(Hustle $hustle, HustleApplication $application)
    {
        $application->load('professional');
        return view('admin.hustles.applications.show', compact('hustle', 'application'));
    }

    public function updateStatus(Request $request, Hustle $hustle, HustleApplication $application)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:pending,approved,rejected,withdrawn'
            ]);

            $application->update($validated);

            // Get notification service
            $notificationService = app(NotificationService::class);

            if ($validated['status'] === 'approved') {
                // Update hustle status and assign professional
                $hustle->update([
                    'status' => 'approved',
                    'assigned_professional_id' => $application->professional_id
                ]);

                // Send approval notification and email
                $notificationData = [
                    'type' => 'application_approved',
                    'title' => 'Application Approved',
                    'message' => "Your application for '{$hustle->title}' has been approved!",
                    'icon' => 'check-circle',
                    'action_url' => '/dashboard/applications/' . $application->id,
                    'extra_data' => [
                        'hustle_id' => $hustle->id,
                    ]
                ];

                $notificationService->send($notificationData, $application->professional->user);
                Mail::to($application->professional->user->email)
                    ->queue(new ApplicationApproved($hustle, $application->professional));

                // Reject other applications
                $otherApplications = $hustle->applications()
                    ->where('id', '!=', $application->id)
                    ->where('status', '!=', 'rejected')
                    ->get();

                foreach ($otherApplications as $otherApplication) {
                    $otherApplication->update(['status' => 'rejected']);

                    // Send rejection notification and email
                    $rejectionData = [
                        'type' => 'application_rejected',
                        'title' => 'Application Update',
                        'message' => "Your application for '{$hustle->title}' was not selected.",
                        'icon' => 'x-circle',
                        'action_url' => '/dashboard/applications/' . $otherApplication->id,
                        'extra_data' => [
                            'hustle_id' => $hustle->id,
                        ]
                    ];

                    $notificationService->send($rejectionData, $otherApplication->professional->user);
                    Mail::to($otherApplication->professional->user->email)
                        ->queue(new ApplicationRejected($hustle, $otherApplication->professional));
                }
            }

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Application status updated successfully'
                ]);
            }

            return back()->with('success', 'Application status updated successfully.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update application status'
                ], 422);
            }

            return back()->with('error', 'Failed to update application status');
        }
    }
} 
<?php

namespace App\Http\Controllers;

use App\Models\Grit;
use App\Models\GritApplication;
use App\Models\Professional;
use App\Models\User;
use App\Services\NotificationService;
use App\Jobs\SendGritApplicationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewGritApplicationSubmitted;
use App\Notifications\NewGritApplicationNotification;

class GritApplicationController extends Controller
{
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



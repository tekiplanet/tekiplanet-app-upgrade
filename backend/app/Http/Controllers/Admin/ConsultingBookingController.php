<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConsultingBooking;
use App\Models\Professional;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ConsultingBookingStatusUpdated;
use App\Mail\ConsultingBookingCancelled;
use App\Mail\ConsultingBookingExpertAssigned;

class ConsultingBookingController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index(Request $request)
    {
        $bookings = ConsultingBooking::with(['user', 'expert.user', 'timeSlot'])
            ->when($request->search, function($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->whereHas('user', function($q) use ($search) {
                        $q->where('first_name', 'like', "%{$search}%")
                          ->orWhere('last_name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                    });
                });
            })
            ->when($request->status, function($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->payment_status, function($query, $status) {
                $query->where('payment_status', $status);
            })
            ->latest()
            ->paginate(10);

        $experts = Professional::with('user')->get();

        return view('admin.consulting.bookings.index', compact('bookings', 'experts'));
    }

    public function show(ConsultingBooking $booking)
    {
        $booking->load(['user', 'expert.user', 'timeSlot', 'review', 'notifications']);
        $experts = Professional::where('status', 'active')
            ->where('availability_status', 'available')
            ->where('user_id', '!=', $booking->user_id)
            ->with('user')
            ->get();

        $statusColors = [
            'pending' => 'bg-yellow-100 text-yellow-800',
            'confirmed' => 'bg-blue-100 text-blue-800',
            'ongoing' => 'bg-purple-100 text-purple-800',
            'completed' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800'
        ];

        // For debugging - count all professionals vs filtered ones
        $totalProfessionals = Professional::count();
        $activeCount = Professional::where('status', 'active')->count();
        $availableCount = Professional::where('availability_status', 'available')->count();

        // \Log::info('Professional counts', [
        //     'total' => $totalProfessionals,
        //     'active' => $activeCount,
        //     'available' => $availableCount,
        //     'final_filtered_count' => $experts->count()
        // ]);

        return view('admin.consulting.bookings.show', compact('booking', 'experts', 'statusColors'));
    }

    public function updateStatus(Request $request, ConsultingBooking $booking)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,ongoing,completed,cancelled',
            'cancellation_reason' => 'required_if:status,cancelled'
        ]);

        $oldStatus = $booking->status;
        
        // Handle cancellation
        if ($validated['status'] === 'cancelled') {
            $booking->update([
                'status' => 'cancelled',
                'cancellation_reason' => $validated['cancellation_reason'],
                'cancelled_at' => now()
            ]);

            // Send cancellation notification
            $this->notificationService->send([
                'type' => 'booking_cancelled',
                'title' => 'Booking Cancelled',
                'message' => "Your booking has been cancelled. Reason: {$validated['cancellation_reason']}",
                'action_url' => "/bookings/{$booking->id}",
                'icon' => 'x-circle'
            ], $booking->user);

            // Send cancellation email
            Mail::to($booking->user->email)
                ->queue(new ConsultingBookingCancelled($booking));

        } else {
            $booking->update(['status' => $validated['status']]);

            // Prepare notification message based on status
            $message = match($validated['status']) {
                'confirmed' => 'Your booking has been confirmed',
                'ongoing' => 'Your consulting session is now in progress',
                'completed' => 'Your consulting session has been marked as completed',
                default => "Your booking status has been updated to " . ucfirst($validated['status'])
            };

            // Send status update notification
            $this->notificationService->send([
                'type' => 'booking_status_updated',
                'title' => 'Booking Status Updated',
                'message' => $message,
                'action_url' => "/bookings/{$booking->id}",
                'icon' => match($validated['status']) {
                    'confirmed' => 'check-circle',
                    'ongoing' => 'play-circle',
                    'completed' => 'check-circle',
                    default => 'info'
                }
            ], $booking->user);

            // Send status update email
            Mail::to($booking->user->email)
                ->queue(new ConsultingBookingStatusUpdated($booking, $oldStatus));
        }

        return response()->json([
            'success' => true,
            'message' => 'Booking status updated successfully'
        ]);
    }

    public function assignExpert(Request $request, ConsultingBooking $booking)
    {
        try {
            $validated = $request->validate([
                'expert_id' => 'required|exists:professionals,id'
            ]);

            // Get the professional
            $expert = Professional::with('user')->find($validated['expert_id']);

            // Check if the professional is the same as the booking user
            if ($expert->user_id === $booking->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'A professional cannot be assigned to their own booking'
                ], 422);
            }

            // Check if this is a reassignment
            $isReassignment = $booking->assigned_expert_id !== null;
            $oldExpertName = $isReassignment ? $booking->expert->user->full_name : null;

            $booking->update([
                'assigned_expert_id' => $validated['expert_id'],
                'expert_assigned_at' => now()
            ]);

            // Prepare notification message
            $message = $isReassignment
                ? "Your booking has been reassigned to a new expert: {$expert->user->full_name}"
                : "An expert has been assigned to your booking: {$expert->user->full_name}";

            // Send notification
            $this->notificationService->send([
                'type' => 'expert_assigned',
                'title' => $isReassignment ? 'Expert Reassigned' : 'Expert Assigned',
                'message' => $message,
                'action_url' => "/bookings/{$booking->id}",
                'icon' => 'user-check'
            ], $booking->user);

            // Send email
            Mail::to($booking->user->email)
                ->queue(new ConsultingBookingExpertAssigned($booking, $isReassignment));

            return response()->json([
                'success' => true,
                'message' => 'Expert assigned successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign expert: ' . $e->getMessage()
            ], 500);
        }
    }
} 
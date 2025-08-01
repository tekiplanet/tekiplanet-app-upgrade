<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConsultingBooking;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use App\Mail\ConsultingBookingReminder;
use Illuminate\Support\Facades\Mail;

class ConsultingBookingReminderController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function sendReminder(Request $request, ConsultingBooking $booking)
    {
        try {
            $validated = $request->validate([
                'note' => 'nullable|string',
                'time_until' => 'required|string'
            ]);

            // Send notification
            $this->notificationService->send([
                'type' => 'booking_reminder',
                'title' => 'Booking Reminder',
                'message' => "Your booking scheduled for {$booking->selected_date->format('M d, Y')} at {$booking->selected_time->format('h:i A')} is in {$validated['time_until']}",
                'action_url' => "/bookings/{$booking->id}",
                'extra_data' => [
                    'note' => $validated['note']
                ]
            ], $booking->user);

            // Send email
            Mail::to($booking->user->email)
                ->queue(new ConsultingBookingReminder($booking, $validated['time_until'], $validated['note']));

            return response()->json([
                'success' => true,
                'message' => 'Reminder sent successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send reminder: ' . $e->getMessage()
            ], 500);
        }
    }
} 
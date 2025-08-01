<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConsultingTimeSlot;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ConsultingTimeSlotController extends Controller
{
    public function index(Request $request)
    {
        $timeSlots = ConsultingTimeSlot::query()
            ->when($request->date, function($query, $date) {
                $query->whereDate('date', $date);
            })
            ->when($request->availability !== null, function($query) use ($request) {
                $query->where('is_available', $request->availability);
            })
            ->orderBy('date')
            ->orderBy('time')
            ->paginate(10);

        return view('admin.consulting.timeslots.index', compact('timeSlots'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
            'time' => 'required',
            'capacity' => 'required|integer|min:1',
            'is_available' => 'boolean'
        ]);

        // Additional check for past date-time combinations
        $dateTime = Carbon::parse($validated['date'] . ' ' . $validated['time']);
        if ($dateTime->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot create time slots in the past'
            ], 422);
        }

        // Check if time slot already exists
        $exists = ConsultingTimeSlot::where('date', $validated['date'])
            ->where('time', $validated['time'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'A time slot already exists for this date and time'
            ], 422);
        }

        ConsultingTimeSlot::create([
            'date' => $validated['date'],
            'time' => $validated['time'],
            'capacity' => $validated['capacity'],
            'is_available' => $validated['is_available'] ?? true,
            'booked_slots' => 0
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Time slot created successfully'
        ]);
    }

    public function bulkCreate(Request $request)
    {
        $validated = $request->validate([
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'days' => 'required|array',
            'times' => 'required|array',
            'capacity' => 'required|integer|min:1'
        ]);

        // Additional check for past times
        $now = Carbon::now();
        $startDate = Carbon::parse($validated['start_date']);
        
        foreach ($validated['times'] as $time) {
            $dateTime = $startDate->copy()->setTimeFromTimeString($time);
            if ($dateTime->isPast()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot create time slots in the past'
                ], 422);
            }
        }

        $endDate = Carbon::parse($validated['end_date']);
        $currentDate = $startDate->copy();

        $existingSlots = [];
        $slotsToCreate = [];
        $createdCount = 0;

        while ($currentDate <= $endDate) {
            if (in_array($currentDate->format('N'), $validated['days'])) {
                foreach ($validated['times'] as $time) {
                    $exists = ConsultingTimeSlot::where('date', $currentDate->format('Y-m-d'))
                        ->where('time', $time)
                        ->exists();

                    if ($exists) {
                        $existingSlots[] = $currentDate->format('Y-m-d') . ' ' . $time;
                    } else {
                        $slotsToCreate[] = [
                            'date' => $currentDate->format('Y-m-d'),
                            'time' => $time,
                            'capacity' => $validated['capacity'],
                            'is_available' => true,
                            'booked_slots' => 0
                        ];
                    }
                }
            }
            $currentDate->addDay();
        }

        // Create the non-existing slots
        foreach ($slotsToCreate as $slot) {
            ConsultingTimeSlot::create($slot);
            $createdCount++;
        }

        $message = [];
        if ($createdCount > 0) {
            $message[] = "$createdCount time slots created successfully";
        }
        if (!empty($existingSlots)) {
            $message[] = count($existingSlots) . " time slots already existed: " . implode(', ', $existingSlots);
        }

        return response()->json([
            'success' => $createdCount > 0,
            'message' => implode('. ', $message),
            'created' => $createdCount,
            'existing' => count($existingSlots)
        ], $createdCount > 0 ? 200 : 422);
    }

    public function update(Request $request, ConsultingTimeSlot $timeSlot)
    {
        // Check if time slot has bookings
        $hasBookings = $timeSlot->booking()->exists();

        $validated = $request->validate([
            'date' => $hasBookings ? 'prohibited' : 'required|date|after_or_equal:today',
            'time' => $hasBookings ? 'prohibited' : 'required',
            'capacity' => 'required|integer|min:1',
            'is_available' => 'boolean'
        ]);

        // If changing date/time, check for duplicates
        if (!$hasBookings && ($request->date !== $timeSlot->date->format('Y-m-d') || $request->time !== $timeSlot->time->format('H:i'))) {
            $exists = ConsultingTimeSlot::where('date', $request->date)
                ->where('time', $request->time)
                ->where('id', '!=', $timeSlot->id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'A time slot already exists for this date and time'
                ], 422);
            }
        }

        // If has bookings, ensure capacity isn't reduced below booked_slots
        if ($hasBookings && $request->capacity < $timeSlot->booked_slots) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot reduce capacity below current bookings'
            ], 422);
        }

        $timeSlot->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Time slot updated successfully'
        ]);
    }

    public function destroy(ConsultingTimeSlot $timeSlot)
    {
        if ($timeSlot->booking()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete time slot with existing bookings'
            ], 422);
        }

        $timeSlot->delete();

        return response()->json([
            'success' => true,
            'message' => 'Time slot deleted successfully'
        ]);
    }

    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:consulting_time_slots,id'
        ]);

        try {
            // Check if any of the selected slots have bookings
            $slotsWithBookings = ConsultingTimeSlot::whereIn('id', $validated['ids'])
                ->whereHas('booking')
                ->exists();

            if ($slotsWithBookings) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete time slots with existing bookings'
                ], 422);
            }

            ConsultingTimeSlot::whereIn('id', $validated['ids'])->delete();

            return response()->json([
                'success' => true,
                'message' => 'Selected time slots deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete time slots: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit(ConsultingTimeSlot $timeSlot)
    {
        return response()->json([
            'date' => $timeSlot->date->format('Y-m-d'),
            'time' => $timeSlot->time->format('H:i'),
            'capacity' => $timeSlot->capacity,
            'is_available' => $timeSlot->is_available,
            'has_bookings' => $timeSlot->booking()->exists()
        ]);
    }
} 
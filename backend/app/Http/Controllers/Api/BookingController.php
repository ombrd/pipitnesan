<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PtBooking;
use App\Models\PtSchedule;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    /**
     * Get current member's bookings
     */
    public function index()
    {
        $user = auth('api')->user();

        $bookings = PtBooking::with(['schedule.trainer'])
            ->where('member_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $bookings
        ]);
    }

    /**
     * Book a PT Schedule
     */
    public function store(Request $request)
    {
        $request->validate([
            'pt_schedule_id' => 'required|exists:pt_schedules,id',
        ]);

        $user = auth('api')->user();

        if ($user->status !== 'active') {
            return response()->json(['error' => 'Your membership is not active. Please renew first.'], 403);
        }

        $schedule = PtSchedule::findOrFail($request->pt_schedule_id);

        // Check if schedule is in the past
        if (now()->toDateString() > $schedule->date) {
             return response()->json(['error' => 'Cannot book a schedule in the past.'], 422);
        }

        // Check quota (count active/done bookings)
        $currentBookingsCount = PtBooking::where('pt_schedule_id', $schedule->id)
            ->whereIn('status', ['booked', 'done'])
            ->count();

        if ($currentBookingsCount >= $schedule->quota) {
            return response()->json(['error' => 'This schedule is already fully booked.'], 409);
        }

        // Check if member already booked this schedule
        $existingBooking = PtBooking::where('pt_schedule_id', $schedule->id)
            ->where('member_id', $user->id)
            ->whereIn('status', ['booked', 'done'])
            ->first();

        if ($existingBooking) {
            return response()->json(['error' => 'You have already booked this schedule.'], 409);
        }

        // Create the booking
        $booking = PtBooking::create([
            'member_id' => $user->id,
            'pt_schedule_id' => $schedule->id,
            'status' => 'booked',
        ]);

        return response()->json([
            'message' => 'Successfully booked the schedule',
            'data' => $booking
        ], 201);
    }
        
    /**
     * Cancel a PT Schedule Booking
     */
    public function cancel(Request $request, $id)
    {
        $request->validate([
            'cancel_reason' => 'required|string|max:1000',
        ]);

        $user = auth('api')->user();
        $booking = PtBooking::with('schedule')->where('member_id', $user->id)->findOrFail($id);

        if ($booking->status === 'cancelled') {
            return response()->json(['error' => 'Booking is already cancelled.'], 400);
        }

        if ($booking->status === 'done') {
            return response()->json(['error' => 'Cannot cancel a completed booking.'], 400);
        }

        // Check if it's at least 1 hour before the schedule
        $scheduleDateTime = \Carbon\Carbon::parse($booking->schedule->date . ' ' . $booking->schedule->time_start);
        
        if (now()->diffInMinutes($scheduleDateTime, false) < 60) {
            return response()->json(['error' => 'You can only cancel a booking at least 1 hour before the schedule start time.'], 400);
        }

        $booking->update([
            'status' => 'cancelled',
            'cancel_reason' => $request->cancel_reason
        ]);

        return response()->json([
            'message' => 'Booking cancelled successfully',
            'data' => $booking
        ]);
    }
}

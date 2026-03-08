<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PersonalTrainer;
use App\Models\PtSchedule;
use Illuminate\Http\Request;

class PTController extends Controller
{
    /**
     * Get a list of active personal trainers (filtered by branch optionally).
     */
    public function index(Request $request)
    {
        $branchId = $request->query('branch_id');

        $query = PersonalTrainer::with('branch')->where('status', true);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $trainers = $query->get();

        return response()->json([
            'data' => $trainers
        ]);
    }

    /**
     * Get schedules for a specific PT.
     */
    public function schedules(Request $request, $id)
    {
        $trainer = PersonalTrainer::findOrFail($id);
        $user = auth('api')->user();

        $query = PtSchedule::where('personal_trainer_id', $trainer->id)
            ->withCount(['bookings as active_bookings_count' => function($q) {
                $q->whereIn('status', ['booked', 'done']);
            }])
            ->where('date', '>=', now()->toDateString())
            ->whereDoesntHave('bookings', function($q) use ($user) {
                $q->where('member_id', $user->id)
                  ->whereIn('status', ['booked', 'done']);
            })
            ->orderBy('date', 'asc')
            ->orderBy('time_start', 'asc');

        if ($request->has('date')) {
            $query->whereDate('date', $request->query('date'));
        }

        $schedules = $query->get()->map(function($schedule) {
            $schedule->remaining_quota = max(0, $schedule->quota - $schedule->active_bookings_count);
            return $schedule;
        });

        return response()->json([
            'data' => $schedules
        ]);
    }
}

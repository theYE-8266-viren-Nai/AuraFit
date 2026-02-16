<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WorkoutPlan;
use Illuminate\Http\Request;

class WorkoutPlanController extends Controller
{
    public function index(Request $request)
    {
        $query = WorkoutPlan::with(['trainer.user', 'member.user']);

        // Filter by trainer if user is trainer
        if ($request->user()->isTrainer()) {
            $query->where('trainer_id', $request->user()->trainer->id);
        }

        $workoutPlans = $query->get();
        return response()->json($workoutPlans);
    }

    public function store(Request $request)
    {
        $request->validate([
            'member_id' => 'required|exists:members,id',
            'description' => 'required|string',
        ]);
\Log::info('Creating membership/workout plan', $request->all());
        $trainerId = $request->user()->trainer->id ?? $request->trainer_id;

        if (!$trainerId) {
            return response()->json(['message' => 'Trainer ID required'], 400);
        }

        $workoutPlan = WorkoutPlan::create([
            'trainer_id' => $trainerId,
            'member_id' => $request->member_id,
            'description' => $request->description,
        ]);

        $workoutPlan->load(['trainer.user', 'member.user']);

        return response()->json($workoutPlan, 201);
    }

    public function show($id)
    {
        $workoutPlan = WorkoutPlan::with(['trainer.user', 'member.user'])->findOrFail($id);
        return response()->json($workoutPlan);
    }

    public function update(Request $request, $id)
    {
        $workoutPlan = WorkoutPlan::findOrFail($id);

        $request->validate([
            'description' => 'required|string',
        ]);

        $workoutPlan->update([
            'description' => $request->description,
        ]);

        return response()->json($workoutPlan);
    }

    public function destroy($id)
    {
        $workoutPlan = WorkoutPlan::findOrFail($id);
        $workoutPlan->delete();

        return response()->json(['message' => 'Workout plan deleted successfully']);
    }

    public function myWorkoutPlans(Request $request)
    {
        $member = $request->user()->member;
        
        if (!$member) {
            return response()->json(['message' => 'Member profile not found'], 404);
        }

        $workoutPlans = WorkoutPlan::with(['trainer.user'])
            ->where('member_id', $member->id)
            ->get();

        return response()->json($workoutPlans);
    }
}
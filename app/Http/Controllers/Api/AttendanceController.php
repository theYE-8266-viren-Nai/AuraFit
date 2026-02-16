<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index()
    {
        $attendances = Attendance::with('member.user')
            ->orderBy('date', 'desc')
            ->get();
        
        return response()->json($attendances);
    }

    public function store(Request $request)
    {
        $request->validate([
            'member_id' => 'required|exists:members,id',
            'date' => 'sometimes|date',
            'check_in' => 'sometimes|date_format:H:i',
            'status' => 'sometimes|string',
        ]);

        $attendance = Attendance::create([
            'member_id' => $request->member_id,
            'date' => $request->date ?? Carbon::today(),
            'check_in' => $request->check_in ?? Carbon::now()->format('H:i'),
            'status' => $request->status ?? 'present',
        ]);

        $attendance->load('member.user');

        return response()->json($attendance, 201);
    }

    public function update(Request $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        $request->validate([
            'check_out' => 'sometimes|date_format:H:i',
            'status' => 'sometimes|string',
        ]);

        $attendance->update($request->only(['check_out', 'status']));

        return response()->json($attendance);
    }

    public function markAttendance(Request $request)
    {
        $member = $request->user()->member;
        
        if (!$member) {
            return response()->json(['message' => 'Member profile not found'], 404);
        }

        // Check if already marked today
        $existingAttendance = Attendance::where('member_id', $member->id)
            ->whereDate('date', Carbon::today())
            ->first();

        if ($existingAttendance) {
            // Update check-out time
            $existingAttendance->update([
                'check_out' => Carbon::now()->format('H:i'),
            ]);
            return response()->json($existingAttendance);
        }

        // Create new attendance
        $attendance = Attendance::create([
            'member_id' => $member->id,
            'date' => Carbon::today(),
            'check_in' => Carbon::now()->format('H:i'),
            'status' => 'present',
        ]);

        return response()->json($attendance, 201);
    }

    public function myAttendance(Request $request)
    {
        $member = $request->user()->member;
        
        if (!$member) {
            return response()->json(['message' => 'Member profile not found'], 404);
        }

        $attendances = Attendance::where('member_id', $member->id)
            ->orderBy('date', 'desc')
            ->get();

        return response()->json($attendances);
    }
}
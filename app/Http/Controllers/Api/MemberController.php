<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MemberController extends Controller
{
    public function index()
    {
        $members = Member::with(['user', 'activeMembership'])->get();
        return response()->json($members);
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'name' => 'required|string',
            'age' => 'required|integer',
            'gender' => 'required|in:male,female,other',
            'phone' => 'required|string',
        ]);

        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'member',
        ]);

        $member = Member::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'age' => $request->age,
            'gender' => $request->gender,
            'phone' => $request->phone,
        ]);

        $member->load('user');

        return response()->json($member, 201);
    }

    public function show($id)
    {
        $member = Member::with(['user', 'memberships', 'workoutPlans.trainer.user', 'payments', 'attendances'])
            ->findOrFail($id);
        
        return response()->json($member);
    }

    public function update(Request $request, $id)
    {
        $member = Member::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string',
            'age' => 'sometimes|integer',
            'gender' => 'sometimes|in:male,female,other',
            'phone' => 'sometimes|string',
            'username' => 'sometimes|string|unique:users,username,' . $member->user_id,
            'email' => 'sometimes|email|unique:users,email,' . $member->user_id,
        ]);

        $member->update($request->only(['name', 'age', 'gender', 'phone']));

        if ($request->has('username') || $request->has('email')) {
            $member->user->update($request->only(['username', 'email']));
        }

        $member->load('user');

        return response()->json($member);
    }

    public function destroy($id)
    {
        $member = Member::findOrFail($id);
        $member->user->delete(); // This will cascade delete member

        return response()->json(['message' => 'Member deleted successfully']);
    }

    public function profile(Request $request)
    {
        $member = $request->user()->member;
        
        if (!$member) {
            return response()->json(['message' => 'Member profile not found'], 404);
        }

        $member->load(['memberships', 'workoutPlans.trainer.user', 'payments', 'attendances']);
        
        return response()->json($member);
    }
}
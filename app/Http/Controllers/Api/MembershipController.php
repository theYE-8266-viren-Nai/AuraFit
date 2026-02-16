<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MembershipController extends Controller
{
    public function index()
    {
        $memberships = Membership::with('member.user')->get();
        return response()->json($memberships);
    }

    public function store(Request $request)
    {

        $request->validate([
            'member_id' => 'required|exists:members,id',
            'type' => 'required|string',
            'duration' => 'required|integer',
            'fee' => 'required|numeric',
        ]);
\Log::info('Creating membership/workout plan', $request->all());
        $startDate = Carbon::today();
        $endDate = $startDate->copy()->addDays($request->duration);

        $membership = Membership::create([
            'member_id' => $request->member_id,
            'type' => $request->type,
            'duration' => $request->duration,
            'fee' => $request->fee,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'active',
        ]);

        $membership->load('member.user');

        return response()->json($membership, 201);
    }

    public function show($id)
    {
        $membership = Membership::with(['member.user', 'payments'])->findOrFail($id);
        return response()->json($membership);
    }

    public function update(Request $request, $id)
    {
        $membership = Membership::findOrFail($id);

        $request->validate([
            'status' => 'sometimes|in:active,expired,cancelled',
            'end_date' => 'sometimes|date',
        ]);

        $membership->update($request->only(['status', 'end_date']));

        return response()->json($membership);
    }

    public function destroy($id)
    {
        $membership = Membership::findOrFail($id);
        $membership->delete();

        return response()->json(['message' => 'Membership deleted successfully']);
    }

    public function membershipStatus(Request $request)
    {
        $member = $request->user()->member;
        
        if (!$member) {
            return response()->json(['message' => 'Member profile not found'], 404);
        }

        $activeMembership = $member->activeMembership;

        return response()->json([
            'member' => $member,
            'active_membership' => $activeMembership,
            'has_active_membership' => $activeMembership ? true : false,
        ]);
    }
}
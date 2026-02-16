<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::with(['member.user', 'membership'])->get();
        return response()->json($payments);
    }

    public function store(Request $request)
    {
        $request->validate([
            'member_id' => 'required|exists:members,id',
            'membership_id' => 'nullable|exists:memberships,id',
            'amount' => 'required|numeric',
            'method' => 'required|string',
        ]);

        $payment = Payment::create([
            'member_id' => $request->member_id,
            'membership_id' => $request->membership_id,
            'amount' => $request->amount,
            'date' => Carbon::today(),
            'method' => $request->method,
            'status' => $request->status ?? 'completed',
        ]);

        $payment->load(['member.user', 'membership']);

        return response()->json($payment, 201);
    }

    public function show($id)
    {
        $payment = Payment::with(['member.user', 'membership'])->findOrFail($id);
        return response()->json($payment);
    }

    public function generateReceipt($id)
    {
        $payment = Payment::with(['member.user', 'membership'])->findOrFail($id);

        $receipt = [
            'receipt_number' => 'REC-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT),
            'date' => $payment->date->format('Y-m-d'),
            'member_name' => $payment->member->name,
            'amount' => $payment->amount,
            'method' => $payment->method,
            'status' => $payment->status,
            'membership_type' => $payment->membership ? $payment->membership->type : 'N/A',
        ];

        return response()->json($receipt);
    }

    public function myPayments(Request $request)
    {
        $member = $request->user()->member;
        
        if (!$member) {
            return response()->json(['message' => 'Member profile not found'], 404);
        }

        $payments = Payment::with('membership')
            ->where('member_id', $member->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($payments);
    }
}
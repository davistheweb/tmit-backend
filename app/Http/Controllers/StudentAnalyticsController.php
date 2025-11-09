<?php

namespace App\Http\Controllers;

use App\Models\FeeStructure;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentAnalyticsController extends Controller
{
    
public function getOverallPaymentSummary(Request $request)
{
    $student = Auth::user();

    $payments = Payment::where('student_id', $student->id)
        ->with(['feeStructure'])
        ->get();

    $summary = [
        'total_paid' => $payments->where('status', 'success')->sum('amount'),
        'total_pending' => $payments->where('status', 'pending')->sum('amount'),
        'successful_payments' => $payments->where('status', 'success')->count(),
        'pending_payments' => $payments->where('status', 'pending')->count(),
        'failed_payments' => $payments->where('status', 'failed')->count(),

        // Extra breakdown by fee category
        'school_fees_paid' => $payments->where('status', 'success')
            ->whereIn('feeStructure.fee_type', ['school'])
            ->sum('amount'),

        'hostel_fees_paid' => $payments->where('status', 'success')
            ->where('feeStructure.fee_type', 'hostel')
            ->sum('amount'),

        'acceptance_fee_paid' => $student->acceptance_fee_paid ?? false
    ];

    return response()->json([
        'success' => true,
        'data' => $summary
    ]);
}



public function getAllPayments(Request $request)
{
    $student = Auth::user();

    $payments = Payment::where('student_id', $student->id)
        ->with(['feeStructure.department', 'session'])
        ->orderBy('created_at', 'desc')
        ->get();

    return response()->json([
        'success' => true,
        'data' => $payments
    ]);
}


    // Get outstanding fees
    public function getOutstandingFees(Request $request)
    {
        $student = Auth::user();

        $validated = $request->validate([
            'session_id' => 'required|exists:school_sessions,id',
        ]);

        // Get all fee structures for student's department and level
        $feeStructures = FeeStructure::where('session_id', $validated['session_id'])
            ->where('department_id', $student->department_id)
            ->where('level', $student->current_level)
            ->with(['department', 'session'])
            ->get();

        $outstanding = [];

        foreach ($feeStructures as $fee) {
            // Check if acceptance fee is required but not paid
            if ($fee->fee_type !== 'acceptance' && !$student->acceptance_fee_paid) {
                continue; // Skip non-acceptance fees if acceptance not paid
            }

            // Skip optional fees (hostel) if you want
            // if ($fee->fee_type === 'hostel' && !$fee->is_mandatory) {
            //     continue;
            // }

            // Check if fee is paid
            $payment = Payment::where('student_id', $student->id)
                ->where('fee_structure_id', $fee->id)
                ->where('session_id', $validated['session_id'])
                ->where('status', 'success')
                ->first();

            if (!$payment) {
                $outstanding[] = [
                    'fee_structure' => $fee,
                    'status' => 'unpaid',
                    'is_mandatory' => $fee->is_mandatory,
                    'fee_type' => $fee->fee_type,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => $outstanding
        ]);
    }

    // Get payment receipt
    public function getPaymentReceipt($reference)
    {
        $student = Auth::user();

        $payment = Payment::where('reference', $reference)
            ->where('student_id', $student->id)
            ->where('status', 'success')
            ->with(['student', 'feeStructure.department', 'session'])
            ->first();

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment receipt not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $payment
        ]);
    }
}

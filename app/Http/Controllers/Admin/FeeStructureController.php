<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeeStructure;
use Illuminate\Http\Request;

class FeeStructureController extends Controller
{
         public function index(Request $request)
    {
        $query = FeeStructure::with(['department', 'session']);

        if ($request->department_id) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->session_id) {
            $query->where('session_id', $request->session_id);
        }

        if ($request->level) {
            $query->where('level', $request->level);
        }

        if ($request->fee_type) {
            $query->where('fee_type', $request->fee_type);
        }

        $fees = $query->orderBy('created_at', 'desc')->paginate(20);
        return response()->json([
            'success' => true,
            'data' => $fees
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'session_id' => 'required|exists:school_sessions,id',
            'level' => 'required|integer|in:100,200,300,400,500,600',
            'fee_type' => 'required|in:school,acceptance,hostel',
            'amount' => 'required|numeric|min:0',
            'installment_first' => 'nullable|numeric|min:0',
            'installment_second' => 'nullable|numeric|min:0',
            'allow_installment' => 'boolean',
            'is_mandatory' => 'boolean',
            'description' => 'nullable|string'
        ]);

        // Acceptance fees are always mandatory and can't be split
        if ($validated['fee_type'] === 'acceptance') {
            $validated['is_mandatory'] = true;
            $validated['allow_installment'] = false;
            $validated['installment_first'] = null;
            $validated['installment_second'] = null;
        }

        // Check if fee structure already exists
        $exists = FeeStructure::where('department_id', $validated['department_id'])
            ->where('session_id', $validated['session_id'])
            ->where('level', $validated['level'])
            ->where('fee_type', $validated['fee_type'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Fee structure already exists for this department, session, level and fee type'
            ], 422);
        }

        // Validate installment amounts sum up to total
        if (($validated['allow_installment'] ?? false) && 
            isset($validated['installment_first']) && 
            isset($validated['installment_second'])) {
            
            $installmentSum = $validated['installment_first'] + $validated['installment_second'];
            
            if (bccomp($installmentSum, $validated['amount'], 2) !== 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Installment amounts must sum up to total amount',
                    'total' => $validated['amount'],
                    'installment_sum' => $installmentSum
                ], 422);
            }
        }

        $fee = FeeStructure::create($validated);
        return response()->json([
            'success' => true,
            'message' => 'Fee structure created successfully',
            'data' => $fee->load(['department', 'session'])
        ], 201);
    }

    public function show(FeeStructure $feeStructure)
    {
        return response()->json([
            'success' => true,
            'data' => $feeStructure->load(['department', 'session'])
        ]);
    }

    public function update(Request $request, FeeStructure $feeStructure)
    {
        $validated = $request->validate([
            'department_id' => 'sometimes|integer|exists:departments,id',
            'session_id' => 'sometimes|integer|exists:school_sessions,id',
            'level' => 'sometimes|integer|in:100,200,300,400,500,600',
            'fee_type' => 'sometimes|in:school,acceptance,hostel',
            'amount' => 'sometimes|numeric|min:0',
            'installment_first' => 'sometimes|nullable|numeric|min:0',
            'installment_second' => 'sometimes|nullable|numeric|min:0',
            'allow_installment' => 'sometimes|boolean',
            'is_mandatory' => 'sometimes|boolean',
            'description' => 'sometimes|nullable|string'
        ]);

        // Acceptance fees validation
        $feeType = $validated['fee_type'] ?? $feeStructure->fee_type;
        if ($feeType === 'acceptance') {
            $validated['is_mandatory'] = true;
            $validated['allow_installment'] = false;
            $validated['installment_first'] = null;
            $validated['installment_second'] = null;
        }

        $amount = $validated['amount'] ?? $feeStructure->amount;
        $installmentFirst = $validated['installment_first'] ?? $feeStructure->installment_first;
        $installmentSecond = $validated['installment_second'] ?? $feeStructure->installment_second;
        $allowInstallment = $validated['allow_installment'] ?? $feeStructure->allow_installment;

        if ($allowInstallment && ($installmentFirst || $installmentSecond)) {
            $sum = ($installmentFirst ?? 0) + ($installmentSecond ?? 0);
            if (bccomp($sum, $amount, 2) !== 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Installment amounts must sum up to the total amount'
                ], 422);
            }
        }

        $feeStructure->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Fee structure updated successfully',
            'data' => $feeStructure->load(['department', 'session'])
        ]);
    }

    public function destroy(FeeStructure $feeStructure)
    {
        $feeStructure->delete();
        return response()->json([
            'success' => true,
            'message' => 'Fee structure deleted successfully'
        ]);
    }

    public function bulkCreate(Request $request)
    {
        $validated = $request->validate([
            'session_id' => 'required|exists:school_sessions,id',
            'fees' => 'required|array',
            'fees.*.department_id' => 'required|exists:departments,id',
            'fees.*.level' => 'required|integer|in:100,200,300,400,500,600',
            'fees.*.fee_type' => 'required|in:school,acceptance,hostel',
            'fees.*.amount' => 'required|numeric|min:0',
            'fees.*.installment_first' => 'nullable|numeric|min:0',
            'fees.*.installment_second' => 'nullable|numeric|min:0',
            'fees.*.allow_installment' => 'boolean',
            'fees.*.is_mandatory' => 'boolean',
            'fees.*.description' => 'nullable|string',
        ]);

        $created = [];
        $errors = [];

        foreach ($validated['fees'] as $index => $feeData) {
            $feeData['session_id'] = $validated['session_id'];
            
            // Acceptance fee validation
            if ($feeData['fee_type'] === 'acceptance') {
                $feeData['is_mandatory'] = true;
                $feeData['allow_installment'] = false;
                $feeData['installment_first'] = null;
                $feeData['installment_second'] = null;
            }
            
            // Check if already exists
            $exists = FeeStructure::where('department_id', $feeData['department_id'])
                ->where('session_id', $feeData['session_id'])
                ->where('level', $feeData['level'])
                ->where('fee_type', $feeData['fee_type'])
                ->exists();

            if ($exists) {
                $errors[] = "Fee at index {$index} already exists";
                continue;
            }

            $created[] = FeeStructure::create($feeData);
        }

        return response()->json([
            'success' => true,
            'message' => 'Bulk creation completed',
            'created' => count($created),
            'errors' => $errors,
            'data' => $created
        ], 201);
    }

}

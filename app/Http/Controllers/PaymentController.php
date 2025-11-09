<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\FeeStructure;
use App\Models\Student;
use App\Models\Session;
use App\Services\PaystackService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    protected $paystack;

    public function __construct(PaystackService $paystack)
    {
        $this->paystack = $paystack;
    }

    public function getAvailableFees(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'session_id' => 'required|exists:school_sessions,id',
        ]);

        $student = Student::findOrFail($validated['student_id']);

        $feeStructures = FeeStructure::where('session_id', $validated['session_id'])
            ->where('department_id', $student->department_id)
            ->where('level', $student->current_level)
            ->with(['department', 'session'])
            ->get();

        if ($feeStructures->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No fee structures found for your department and level',
                'debug' => [
                    'department_id' => $student->department_id,
                    'current_level' => $student->current_level,
                    'session_id' => $validated['session_id']
                ]
            ], 404);
        }

        $availableFees = [];

        foreach ($feeStructures as $fee) {
            if ($fee->fee_type === 'acceptance') {
                if (!$student->acceptance_fee_paid) {
                    $payment = Payment::where('student_id', $student->id)
                        ->where('fee_structure_id', $fee->id)
                        ->where('status', 'success')
                        ->first();

                    $availableFees[] = [
                        'fee_structure' => $fee,
                        'is_paid' => $payment ? true : false,
                        'payment' => $payment,
                        'can_pay' => !$payment,
                        'message' => $payment 
                            ? 'Acceptance fee paid' 
                            : 'You must pay acceptance fee to continue',
                    ];
                }
                continue;
            }

            if (!$student->acceptance_fee_paid) {
                $availableFees[] = [
                    'fee_structure' => $fee,
                    'is_paid' => false,
                    'payment' => null,
                    'can_pay' => false,
                    'message' => 'You must pay acceptance fee before paying this fee',
                ];
                continue;
            }

            $payment = Payment::where('student_id', $student->id)
                ->where('fee_structure_id', $fee->id)
                ->where('session_id', $validated['session_id'])
                ->where('status', 'success')
                ->first();

            $availableFees[] = [
                'fee_structure' => $fee,
                'is_paid' => $payment ? true : false,
                'payment' => $payment,
                'can_pay' => !$payment,
                'message' => $payment ? 'Fee already paid' : 'Available for payment',
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'acceptance_fee_paid' => $student->acceptance_fee_paid,
                'fees' => $availableFees
            ]
        ]);
    }

    public function initiate(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'fee_structure_id' => 'required|exists:fee_structures,id',
            'payment_type' => 'required|in:full,installment_first,installment_second',
        ]);

        $student = Student::findOrFail($request->student_id);
        $feeStructure = FeeStructure::findOrFail($request->fee_structure_id);

        if ($feeStructure->fee_type !== 'acceptance' && !$student->acceptance_fee_paid) {
            return response()->json([
                'success' => false,
                'message' => 'You must pay acceptance fee before paying other fees'
            ], 422);
        }

        $existingPayment = Payment::where('student_id', $student->id)
            ->where('fee_structure_id', $feeStructure->id)
            ->where('payment_type', $request->payment_type)
            ->where('status', 'success')
            ->first();

        if ($existingPayment) {
            return response()->json([
                'success' => false,
                'message' => 'You have already paid this fee',
                'payment' => $existingPayment
            ], 422);
        }

        $amount = match($request->payment_type) {
            'full' => $feeStructure->amount,
            'installment_first' => $feeStructure->installment_first,
            'installment_second' => $feeStructure->installment_second,
            default => $feeStructure->amount
        };

        if (!$amount || $amount <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid payment amount for the selected payment type'
            ], 422);
        }

        Payment::where('student_id', $student->id)
            ->where('fee_structure_id', $feeStructure->id)
            ->where('payment_type', $request->payment_type)
            ->where('status', 'pending')
            ->delete();

        $reference = 'TXN_' . time() . '_' . strtoupper(substr(md5(uniqid($student->id, true)), 0, 8));

        $payment = Payment::create([
            'student_id' => $student->id,
            'fee_structure_id' => $feeStructure->id,
            'session_id' => $feeStructure->session_id,
            'reference' => $reference,
            'amount' => $amount,
            'payment_type' => $request->payment_type,
            'status' => 'pending',
        ]);

        $data = [
            'email' => $student->email,
            'amount' => $payment->amount * 100,
            'reference' => $payment->reference,
            'callback_url' => config('app.frontend_url') . '/payment/callback',
            'metadata' => [
                'student_id' => $student->id,
                'student_name' => $student->name,
                'matric_number' => $student->reg_number ?? 'N/A',
                'fee_structure_id' => $feeStructure->id,
                'payment_id' => $payment->id,
                'payment_type' => $request->payment_type,
                'fee_type' => $feeStructure->fee_type,
                'level' => $feeStructure->level,
                'department' => $student->department->name ?? 'N/A',
                'phone' => $student->phone,
            ],
            'bearer' => 'subaccount'
        ];

        try {
            $response = $this->paystack->initializeTransaction($data);
            
            if (!$response['status']) {
                $payment->delete();
                
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to initialize payment with Paystack',
                    'error' => $response['message'] ?? 'Unknown error'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment initiated successfully',
                'data' => [
                    'payment' => $payment,
                    'authorization_url' => $response['data']['authorization_url'],
                    'access_code' => $response['data']['access_code'],
                    'reference' => $payment->reference
                ]
            ]);
        } catch (\Exception $e) {
            $payment->delete();
            Log::error('Payment initialization error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while initiating payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ✅ FIXED: Verify BEFORE redirecting
    public function callback(Request $request)
    {
        $reference = $request->query('reference');
        
        if (!$reference) {
            return redirect(config('app.frontend_url') . '/payment/failed?error=no_reference');
        }

        try {
            // ✅ VERIFY PAYMENT FIRST
            $response = $this->paystack->verifyTransaction($reference);
            
            Log::info('Paystack Callback Response', [
                'reference' => $reference,
                'response' => $response
            ]);

            if ($response['status'] && $response['data']['status'] === 'success') {
                $payment = Payment::where('reference', $reference)->first();
                
                if (!$payment) {
                    Log::error('Payment not found for reference: ' . $reference);
                    return redirect(config('app.frontend_url') . '/payment/failed?error=payment_not_found');
                }

                // ✅ UPDATE PAYMENT STATUS
                if ($payment->status !== 'success') {
                    $payment->update([
                        'status' => 'success',
                        'gateway_response' => $response['data']['gateway_response'] ?? 'Successful',
                        'channel' => $response['data']['channel'] ?? 'card',
                        'currency' => $response['data']['currency'] ?? 'NGN',
                        'paid_at' => isset($response['data']['paid_at']) 
                            ? Carbon::parse($response['data']['paid_at']) 
                            : now(),
                    ]);

                    Log::info('Payment updated to success', [
                        'payment_id' => $payment->id,
                        'reference' => $reference
                    ]);

                    // ✅ UPDATE ACCEPTANCE FEE STATUS
                    $feeStructure = $payment->feeStructure;
                    if ($feeStructure && $feeStructure->fee_type === 'acceptance') {
                        $payment->student->update(['acceptance_fee_paid' => true]);
                        Log::info('Acceptance fee marked as paid for student: ' . $payment->student_id);
                    }
                }

                // ✅ NOW REDIRECT WITH SUCCESS
                $queryParams = http_build_query([
                    'reference' => $reference,
                    'status' => 'success',
                    'amount' => $response['data']['amount'] / 100,
                    'fee_type' => $payment->feeStructure->fee_type ?? 'unknown',
                ]);

                return redirect(config('app.frontend_url') . '/payment/callback?' . $queryParams);
            }

            // ✅ PAYMENT FAILED
            Log::warning('Payment verification failed', [
                'reference' => $reference,
                'paystack_status' => $response['data']['status'] ?? 'unknown'
            ]);

            $queryParams = http_build_query([
                'reference' => $reference,
                'status' => 'failed',
            ]);

            return redirect(config('app.frontend_url') . '/payment/callback?' . $queryParams);
            
        } catch (\Exception $e) {
            Log::error('Payment callback error: ' . $e->getMessage(), [
                'reference' => $reference,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect(config('app.frontend_url') . '/payment/failed?error=verification_failed');
        }
    }

    public function webhook(Request $request)
    {
        $signature = $request->header('x-paystack-signature');
        $payload = $request->getContent();

        if ($signature !== hash_hmac('sha512', $payload, config('services.paystack.secret_key'))) {
            Log::warning('Invalid webhook signature');
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $event = json_decode($payload, true);
        
        Log::info('Webhook received', ['event' => $event['event'] ?? 'unknown']);

        if ($event['event'] === 'charge.success') {
            $reference = $event['data']['reference'];
            $payment = Payment::where('reference', $reference)->first();

            if ($payment && $payment->status !== 'success') {
                $paidAmount = $event['data']['amount'] / 100;
                $fees = $event['data']['fees'] ?? 0;
                $actualAmount = $paidAmount - ($fees / 100);

                $payment->update([
                    'status' => 'success',
                    'gateway_response' => $event['data']['gateway_response'] ?? 'Successful',
                    'channel' => $event['data']['channel'] ?? 'card',
                    'currency' => $event['data']['currency'] ?? 'NGN',
                    'paid_at' => isset($event['data']['paid_at']) 
                        ? Carbon::parse($event['data']['paid_at']) 
                        : now(),
                ]);

                Log::info("Webhook: Payment processed", [
                    'reference' => $reference,
                    'gross_amount' => $paidAmount,
                    'paystack_fees' => $fees / 100,
                    'net_amount' => $actualAmount,
                ]);

                $feeStructure = $payment->feeStructure;
                if ($feeStructure && $feeStructure->fee_type === 'acceptance') {
                    $payment->student->update(['acceptance_fee_paid' => true]);
                    Log::info('Webhook: Acceptance fee marked as paid for student: ' . $payment->student_id);
                }
            }
        }

        return response()->json(['status' => 'ok']);
    }

    // ✅ SIMPLIFIED: Just return the payment record
    public function verifyPayment($reference)
    {
        try {
            $payment = Payment::where('reference', $reference)
                ->with(['student', 'feeStructure', 'session'])
                ->first();

            if (!$payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment not found'
                ], 404);
            }

            // ✅ If already success, just return it
            if ($payment->status === 'success') {
                return response()->json([
                    'success' => true,
                    'message' => 'Payment already verified',
                    'data' => $payment
                ]);
            }

            // ✅ If still pending, try to verify with Paystack
            $response = $this->paystack->verifyTransaction($reference);

            if ($response['status'] && $response['data']['status'] === 'success') {
                $payment->update([
                    'status' => 'success',
                    'gateway_response' => $response['data']['gateway_response'] ?? 'Successful',
                    'channel' => $response['data']['channel'] ?? 'card',
                    'currency' => $response['data']['currency'] ?? 'NGN',
                    'paid_at' => isset($response['data']['paid_at']) 
                        ? Carbon::parse($response['data']['paid_at']) 
                        : now(),
                ]);

                if ($payment->feeStructure && $payment->feeStructure->fee_type === 'acceptance') {
                    $payment->student->update(['acceptance_fee_paid' => true]);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Payment verified successfully',
                    'data' => $payment->fresh(['student', 'feeStructure', 'session'])
                ]);
            } else {
                // Mark as failed
                $payment->update([
                    'status' => 'failed',
                    'gateway_response' => $response['data']['gateway_response'] ?? 'Failed'
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Payment verification failed',
                    'data' => $payment
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Verify payment error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error verifying payment: ' . $e->getMessage()
            ], 500);
        }
    }
}
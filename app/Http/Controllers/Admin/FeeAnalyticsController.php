<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Session;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FeeAnalyticsController extends Controller
{
    public function dashboardOverview(Request $request)
    {
        $validated = $request->validate([
            'session_id' => 'nullable|exists:school_sessions,id',
        ]);

        $sessionId = $validated['session_id'] ?? null;

        // If no session provided, get active session
        if (!$sessionId) {
            $activeSession = Session::where('is_active', true)->first();
            $sessionId = $activeSession ? $activeSession->id : null;
        }

        // Total students
        $totalStudents = Student::count();

        // Payments query
        $paymentsQuery = Payment::query();
        if ($sessionId) {
            $paymentsQuery->where('session_id', $sessionId);
        }

        $totalRevenue = $paymentsQuery->where('status', 'success')->sum('amount');
        $totalPayments = $paymentsQuery->where('status', 'success')->count();
        $pendingPayments = $paymentsQuery->where('status', 'pending')->count();
        $failedPayments = $paymentsQuery->where('status', 'failed')->count();

        // Students who have paid acceptance fee
        $acceptanceFeePaid = Student::where('acceptance_fee_paid', true)->count();

        $revenueByFeeType = Payment::from('payments as p')
    ->where('p.status', 'success')
    ->when($sessionId, fn($q) => $q->where('p.session_id', $sessionId))
    ->join('fee_structures as f', 'p.fee_structure_id', '=', 'f.id')
    ->select('f.fee_type', DB::raw('SUM(p.amount) as total'), DB::raw('COUNT(*) as count'))
    ->groupBy('f.fee_type')
    ->get();


        // Payment type breakdown
        $paymentTypeBreakdown = Payment::where('status', 'success')
            ->when($sessionId, fn($q) => $q->where('session_id', $sessionId))
            ->select('payment_type', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
            ->groupBy('payment_type')
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    $item->payment_type => [
                        'count' => $item->count,
                        'total' => number_format($item->total, 2)
                    ]
                ];
            });

        // Recent payments
        $recentPayments = Payment::with(['student', 'feeStructure'])
            ->when($sessionId, fn($q) => $q->where('session_id', $sessionId))
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Top departments by revenue
        // Top departments by revenue
$topDepartments = Payment::from('payments as p')
    ->where('p.status', 'success')
    ->when($sessionId, fn($q) => $q->where('p.session_id', $sessionId))
    ->join('students as s', 'p.student_id', '=', 's.id')
    ->join('departments as d', 's.department_id', '=', 'd.id')
    ->select('d.name as department_name', DB::raw('SUM(p.amount) as total_revenue'))
    ->groupBy('d.name')
    ->orderByDesc('total_revenue')
    ->limit(5)
    ->get();


        return response()->json([
            'success' => true,
            'session_id' => $sessionId,
            'data' => [
                'overview' => [
                    'total_students' => $totalStudents,
                    'acceptance_fee_paid_count' => $acceptanceFeePaid,
                    'acceptance_fee_unpaid_count' => $totalStudents - $acceptanceFeePaid,
                    'total_revenue' => number_format($totalRevenue, 2),
                    'total_payments' => $totalPayments,
                    'pending_payments' => $pendingPayments,
                    'failed_payments' => $failedPayments,
                    'average_payment' => $totalPayments > 0 ? number_format($totalRevenue / $totalPayments, 2) : '0.00',
                ],
                'revenue_by_fee_type' => $revenueByFeeType,
                'payment_type_breakdown' => $paymentTypeBreakdown,
                'recent_payments' => $recentPayments,
                'top_departments' => $topDepartments,
            ]
        ]);
    }

    // Revenue by Session
    public function revenueBySession()
    {
        $revenue = Payment::where('status', 'success')
            ->select('session_id', DB::raw('SUM(amount) as total_revenue'), DB::raw('COUNT(*) as payment_count'))
            ->with('session')
            ->groupBy('session_id')
            ->get()
            ->map(function ($item) {
                return [
                    'session' => $item->session->name ?? 'Unknown',
                    'session_id' => $item->session_id,
                    'total_revenue' => number_format($item->total_revenue, 2),
                    'payment_count' => $item->payment_count,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $revenue
        ]);
    }

    // Filter payments with all criteria
    public function filterPayments(Request $request)
    {
        $validated = $request->validate([
            'session_id' => 'nullable|exists:school_sessions,id',
            'department_id' => 'nullable|exists:departments,id',
            'student_id' => 'nullable|exists:students,id',
            'fee_type' => 'nullable|in:school,acceptance,hostel',
            'month' => 'nullable|integer|min:1|max:12',
            'year' => 'nullable|integer|min:2020',
            'status' => 'nullable|in:pending,success,failed',
            'payment_type' => 'nullable|in:full,installment_first,installment_second',
            'level' => 'nullable|integer|in:100,200,300,400,500,600',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'per_page' => 'nullable|integer|min:10|max:100',
        ]);

        $query = Payment::with(['student.department', 'feeStructure', 'session']);

        // Filter by session
        if ($request->session_id) {
            $query->where('session_id', $request->session_id);
        }

        // Filter by department
        if ($request->department_id) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        // Filter by student
        if ($request->student_id) {
            $query->where('student_id', $request->student_id);
        }

        // Filter by fee type
        if ($request->fee_type) {
            $query->whereHas('feeStructure', function ($q) use ($request) {
                $q->where('fee_type', $request->fee_type);
            });
        }

        // Filter by month and year
        if ($request->month && $request->year) {
            $query->whereMonth('paid_at', $request->month)
                  ->whereYear('paid_at', $request->year);
        } elseif ($request->year) {
            $query->whereYear('paid_at', $request->year);
        } elseif ($request->month) {
            $query->whereMonth('paid_at', $request->month);
        }

        // Filter by date range
        if ($request->date_from && $request->date_to) {
            $query->whereBetween('paid_at', [$request->date_from, $request->date_to]);
        }

        // Filter by status
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Filter by payment type
        if ($request->payment_type) {
            $query->where('payment_type', $request->payment_type);
        }

        // Filter by level
        if ($request->level) {
            $query->whereHas('feeStructure', function ($q) use ($request) {
                $q->where('level', $request->level);
            });
        }

        $perPage = $request->per_page ?? 20;
        $payments = $query->orderBy('created_at', 'desc')->paginate($perPage);

        // Calculate summary
        $summaryQuery = clone $query;
        $summary = [
            'total_amount' => $summaryQuery->where('status', 'success')->sum('amount'),
            'total_count' => $payments->total(),
            'successful_count' => $summaryQuery->where('status', 'success')->count(),
            'pending_count' => $summaryQuery->where('status', 'pending')->count(),
            'failed_count' => $summaryQuery->where('status', 'failed')->count(),
        ];

        return response()->json([
            'success' => true,
            'summary' => $summary,
            'payments' => $payments
        ]);
    }

}

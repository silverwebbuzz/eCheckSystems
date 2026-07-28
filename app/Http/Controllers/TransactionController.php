<?php

namespace App\Http\Controllers;

use App\Models\HowItWork;
use App\Models\PaymentHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TransactionController extends Controller
{
    public function index()
    {
        return view('admin.transactions.index');
    }

    public function list(Request $request)
    {
        $query = PaymentHistory::query();

        if ($request->filter == 'today') {
            $query->whereDate('PaymentDate', Carbon::today());
        }

        if ($request->filter == 'yesterday') {
            $query->whereDate('PaymentDate', Carbon::yesterday());
        }

        if ($request->filter == 'custom' && $request->start_date && $request->end_date) {
            $query->whereBetween('PaymentDate', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay()
            ]);
        }

        $payments = $query->orderBy('PaymentHistoryID', 'desc')->get();

        return datatables()->of($payments)
            ->addIndexColumn()
            ->addColumn('paymentDate', function ($row) {
                if (empty($row->PaymentDate)) {
                    return '';
                }

                // convert string to Carbon
                return Carbon::parse($row->PaymentDate)->format('m/d/Y');
            })
            ->addColumn('firstName', function ($row) {
                return $row?->subscription?->user?->FirstName ?? '';
            })
            ->addColumn('lastName', function ($row) {
                return $row?->subscription?->user?->LastName ?? '';
            })
            ->addColumn('email', function ($row) {
                return $row?->subscription?->user?->Email ?? '';
            })
            ->addColumn('details', function ($row) {
                return $row?->Remarks ?? '';
            })
            ->editColumn('PaymentAmount', function ($row) {
                return '$' . $row->PaymentAmount;
            })
            ->addColumn('status', function ($row) {
                return $row->PaymentStatus == 'Success'
                    ? '<span class="badge bg-label-success">' . $row->PaymentStatus . '</span>'
                    : '<span class="badge bg-label-danger">' . $row->PaymentStatus . '</span>';
            })
            // ->addColumn('actions', function ($row) {
            //     return '<a href="' . route('admin.transactions.view', $row->PaymentHistoryID) . '" class="dropdown-item justify-content-center">
            //                         <i class="ti ti-eye me-1"></i> View
            //                     </a>';
            //     // return '<a href="' . route('admin.suggestions.view', $row->id) . '" class="btn btn-primary">View</a>';
            // })
            ->rawColumns(['paymentDate', 'status'])
            ->make(true);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reservation;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    // API (USED BY VUE)
    public function index(Request $request)
    {
        $from = $request->from;
        $to   = $request->to;

        $query = Reservation::query();

        if ($from && $to) {
            $query->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);
        }

        $confirmed = (clone $query)->where('status', 'confirmed')->count();
        $pending   = (clone $query)->where('status', 'pending')->count();
        $cancelled = (clone $query)->where('status', 'cancelled')->count();
        $returned  = (clone $query)->where('status', 'returned')->count();

        $totalIncome = (clone $query)->where('status', 'confirmed')->sum('price');

        // MONTHLY RESERVATIONS
        $monthlyReservationsRaw = (clone $query)
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // MONTHLY INCOME
        $monthlyIncomeRaw = (clone $query)
            ->where('status', 'confirmed')
            ->selectRaw('MONTH(created_at) as month, SUM(price) as income')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // TOP ITEMS
        $mostRentedItems = (clone $query)
            ->selectRaw('product_id, COUNT(*) as total')
            ->groupBy('product_id')
            ->orderByDesc('total')
            ->with('product:id,item_name')
            ->take(5)
            ->get()
            ->map(function ($row) {
                return [
                    'name'  => optional($row->product)->item_name ?? 'Unknown',
                    'total' => (int) $row->total,
                ];
            })
            ->values();

        $monthLabels = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

        $monthlyReservations = collect(range(1, 12))->map(function ($month) use ($monthlyReservationsRaw, $monthLabels) {
            $found = $monthlyReservationsRaw->firstWhere('month', $month);
            return [
                'month' => $monthLabels[$month - 1],
                'total' => $found ? (int) $found->total : 0,
            ];
        });

        $monthlyIncome = collect(range(1, 12))->map(function ($month) use ($monthlyIncomeRaw, $monthLabels) {
            $found = $monthlyIncomeRaw->firstWhere('month', $month);
            return [
                'month'  => $monthLabels[$month - 1],
                'income' => $found ? (float) $found->income : 0,
            ];
        });

        return response()->json([
            'confirmed' => $confirmed,
            'pending' => $pending,
            'cancelled' => $cancelled,
            'returned' => $returned,
            'totalIncome' => (float) $totalIncome,
            'monthlyReservations' => $monthlyReservations,
            'monthlyIncome' => $monthlyIncome,
            'mostRentedItems' => $mostRentedItems,
        ]);
    }

    // PDF EXPORT
    public function reportPdf(Request $request)
    {
        $from = $request->from;
        $to   = $request->to;

        $query = Reservation::query();

        if ($from && $to) {
            $query->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);
        }

        $confirmed = (clone $query)->where('status', 'confirmed')->count();
        $pending   = (clone $query)->where('status', 'pending')->count();
        $cancelled = (clone $query)->where('status', 'cancelled')->count();
        $returned  = (clone $query)->where('status', 'returned')->count();

        $totalIncome = (clone $query)->where('status', 'confirmed')->sum('price');

        $monthlyRaw = (clone $query)
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as reservations, SUM(price) as income')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $monthLabels = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

        $monthly = collect(range(1, 12))->map(function ($month) use ($monthlyRaw, $monthLabels) {
            $found = $monthlyRaw->firstWhere('month', $month);

            return (object) [
                'month' => $monthLabels[$month - 1],
                'reservations' => $found ? (int) $found->reservations : 0,
                'income' => $found ? (float) $found->income : 0,
            ];
        });

        $mostRentedItems = (clone $query)
            ->selectRaw('product_id, COUNT(*) as total')
            ->groupBy('product_id')
            ->orderByDesc('total')
            ->with('product:id,item_name')
            ->take(5)
            ->get()
            ->map(function ($row) {
                return [
                    'name'  => optional($row->product)->item_name ?? 'Unknown',
                    'total' => (int) $row->total,
                ];
            })
            ->values();

        $pdf = Pdf::loadView('admin.report-pdf', [
            'from' => $from,
            'to' => $to,
            'confirmed' => $confirmed,
            'pending' => $pending,
            'cancelled' => $cancelled,
            'returned' => $returned,
            'totalIncome' => $totalIncome,
            'monthly' => $monthly,
            'mostRentedItems' => $mostRentedItems,
        ])->setPaper('A4', 'portrait');

        return $pdf->stream('report.pdf');
    }
}
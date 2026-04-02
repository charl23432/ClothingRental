<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reservation;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    // API (USED BY VUE)
    public function index()
    {
        $confirmed = Reservation::where('status', 'confirmed')->count();
        $pending   = Reservation::where('status', 'pending')->count();
        $cancelled = Reservation::where('status', 'cancelled')->count();
        $returned  = Reservation::where('status', 'returned')->count();

        $totalIncome = Reservation::where('status', 'confirmed')->sum('price');

        // MONTHLY RESERVATIONS
        $monthlyReservationsRaw = Reservation::selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // MONTHLY INCOME
        $monthlyIncomeRaw = Reservation::where('status', 'confirmed')
            ->selectRaw('MONTH(created_at) as month, SUM(price) as income')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // TOP ITEMS
        $mostRentedItems = Reservation::selectRaw('product_id, COUNT(*) as total')
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
            $query->whereBetween('created_at', [$from, $to]);
        }

        $confirmed = (clone $query)->where('status', 'confirmed')->count();
        $pending   = (clone $query)->where('status', 'pending')->count();
        $cancelled = (clone $query)->where('status', 'cancelled')->count();
        $returned  = (clone $query)->where('status', 'returned')->count();

        $totalIncome = (clone $query)->where('status', 'confirmed')->sum('price');

        // 🔥 ADD MONTHLY DATA (IMPORTANT FIX)
        $monthly = (clone $query)
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as reservations, SUM(price) as income')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // 🔥 FIX TOP ITEMS FORMAT
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
            });

        // 🔥 LOAD VIEW
        $pdf = Pdf::loadView('admin.report-pdf', [
            'from' => $from,
            'to' => $to,
            'confirmed' => $confirmed,
            'pending' => $pending,
            'cancelled' => $cancelled,
            'returned' => $returned,
            'totalIncome' => $totalIncome,
            'monthly' => $monthly,
            'mostRentedItems' => $mostRentedItems
        ])->setPaper('A4', 'portrait');

        return $pdf->stream('report.pdf');
    }
}
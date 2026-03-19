<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index()
    {
        $totalReservations = Reservation::where('status', 'Confirmed')->count();
        $pendingApprovals = Reservation::where('status', 'Pending')->count();

        $monthlyReservations = Reservation::select(
            DB::raw('MONTH(rent_time) as month'),
            DB::raw('COUNT(*) as total')
        )
        ->where('status', 'Confirmed')
        ->groupBy('month')
        ->pluck('total', 'month')
        ->toArray();

        $monthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        $monthlyData = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthlyData[] = [
                'month' => $monthLabels[$m - 1],
                'count' => $monthlyReservations[$m] ?? 0,
            ];
        }

        $menCategories = ['men_prom', 'men_tuxedo'];
        $womenCategories = ['women_prom', 'women_wedding'];

        $menCount = Reservation::whereHas('product', function ($query) use ($menCategories) {
            $query->whereIn('category', $menCategories);
        })->count();

        $womenCount = Reservation::whereHas('product', function ($query) use ($womenCategories) {
            $query->whereIn('category', $womenCategories);
        })->count();

        $recentReservations = Reservation::with(['user', 'product'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($reservation) {
                return [
                    'id' => $reservation->id,
                    'user_name' => $reservation->user->name ?? 'N/A',
                    'item_name' => $reservation->product->item_name ?? 'Deleted Product',
                    'rent_time' => optional($reservation->rent_time)->format('Y-m-d H:i'),
                    'status' => $reservation->status,
                ];
            });

        return response()->json([
            'totalReservations' => $totalReservations,
            'pendingApprovals' => $pendingApprovals,
            'monthlyData' => $monthlyData,
            'menCount' => $menCount,
            'womenCount' => $womenCount,
            'recentReservations' => $recentReservations,
        ]);
    }
}
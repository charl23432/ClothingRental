<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Models\Reservation;
use App\Notifications\ReservationConfirmed;
use App\Notifications\ReservationReturned;

class ReservationController extends Controller
{
    public function pending()
    {
        $reservations = Reservation::with(['user', 'product'])
            ->where('status', 'Pending')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($reservations);
    }

    public function confirmed()
    {
        $reservations = Reservation::with(['user', 'product'])
            ->whereIn('status', ['Confirmed', 'Returned'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($reservations);
    }

    public function confirm(Reservation $reservation)
    {
        try {
            DB::transaction(function () use ($reservation) {
                if ($reservation->status !== 'Pending') {
                    throw new \Exception('Reservation already processed.');
                }

                $reservation->load('user', 'product');

                if (!$reservation->user) {
                    throw new \Exception('User not found for this reservation.');
                }

                if (!$reservation->product) {
                    throw new \Exception('Product not found.');
                }

                $item = $reservation->product;

                if ($item->quantity <= 0) {
                    throw new \Exception('This item is out of stock.');
                }

                $item->decrement('quantity', 1);
                $item->increment('rented', 1);

                $reservation->update([
                    'status' => 'Confirmed'
                ]);

                Notification::send(
                    $reservation->user,
                    new ReservationConfirmed($reservation->fresh(['user', 'product']))
                );
            });

            return response()->json([
                'message' => 'Reservation confirmed.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function returnItem(Reservation $reservation)
    {
        try {
            DB::transaction(function () use ($reservation) {
                $reservation->load('user', 'product');

                if ($reservation->status === 'Returned') {
                    throw new \Exception('This item was already returned.');
                }

                if ($reservation->status !== 'Confirmed') {
                    throw new \Exception('Only confirmed reservations can be returned.');
                }

                if (!$reservation->product) {
                    throw new \Exception('Product not found.');
                }

                if (!$reservation->user) {
                    throw new \Exception('User not found for this reservation.');
                }

                $item = $reservation->product;

                $item->increment('quantity');

                if ($item->rented > 0) {
                    $item->decrement('rented', 1);
                }

                $reservation->update([
                    'status' => 'Returned'
                ]);

                Notification::send(
                    $reservation->user,
                    new ReservationReturned($reservation->fresh(['user', 'product']))
                );
            });

            return response()->json([
                'message' => 'Item returned.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
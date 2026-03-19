<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CheckoutController extends Controller
{
    public function checkout($id)
    {
        $product = Item::findOrFail($id);

        $availableSizes = collect(
            json_decode($product->sizes, true) ?? []
        );

        return response()->json([
            'product' => $product,
            'availableSizes' => $availableSizes,
        ]);
    }

    public function store(Request $request)
    {
        // ✅ ADD THIS CHECK
        if (!Auth::check()) {
            return response()->json([
                'message' => 'You must be logged in to make a reservation.'
            ], 401);
        }

        $request->validate([
            'product_id' => 'required|exists:items,id',
            'rent_time'  => 'required|date',
            'delivery'   => 'required|in:delivery,pickup',
            'price'      => 'required|numeric',
        ]);

        $item = Item::findOrFail($request->product_id);

        $availableSizes = collect(
            json_decode($item->sizes, true) ?? []
        )
        ->pluck('size')
        ->map(fn ($size) => strtolower(trim($size)))
        ->toArray();

        $request->merge([
            'size' => strtolower(trim($request->size))
        ]);

        $request->validate([
            'size' => ['required', Rule::in($availableSizes)],
        ]);

        if ($request->delivery === 'delivery' && blank($request->gcash_reference)) {
            return response()->json([
                'message' => 'GCash Reference is required for delivery.'
            ], 422);
        }

        if ($item->quantity <= 0) {
            return response()->json([
                'message' => 'Sorry, this item is currently out of stock.'
            ], 422);
        }

        $reservation = DB::transaction(function () use ($request, $item) {
            $deliveryFee = $request->delivery === 'delivery' ? 50 : 0;

            return Reservation::create([
                'user_id'         => Auth::id(), // now guaranteed not null
                'product_id'      => $item->id,
                'size'            => $request->size,
                'rent_time'       => $request->rent_time,
                'delivery'        => $request->delivery,
                'gcash_reference' => $request->gcash_reference,
                'price'           => $request->price + $deliveryFee,
                'status'          => 'Pending',
            ]);
        });

        return response()->json([
            'message' => 'Reservation submitted! Please wait for admin confirmation.',
            'reservation' => $reservation
        ]);
    }
}
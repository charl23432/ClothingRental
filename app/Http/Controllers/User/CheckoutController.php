<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Reservation;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    private function parseSizes($sizes)
    {
        if (is_array($sizes)) {
            return $sizes;
        }

        $decoded = json_decode($sizes, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function getBlockingStatuses()
    {
        return ['pending', 'confirmed', 'rented'];
    }

    private function getAvailableSizes(Item $item)
    {
        $sizes = $this->parseSizes($item->sizes);

        foreach ($sizes as &$size) {
            $sizeName = strtolower($size['size'] ?? '');

            $usedCount = Reservation::where('product_id', $item->id)
                ->whereRaw('LOWER(size) = ?', [$sizeName])
                ->whereIn('status', $this->getBlockingStatuses())
                ->count();

            $stock = (int) ($size['stock'] ?? 0);
            $size['stock'] = $stock;
            $size['available'] = max($stock - $usedCount, 0);
        }

        return $sizes;
    }

    private function getTotalAvailableFromSizes($sizes)
    {
        return collect($sizes)->sum(function ($size) {
            return (int) ($size['available'] ?? 0);
        });
    }

    private function refreshItemQuantity(Item $item)
    {
        $sizes = $this->getAvailableSizes($item);
        $item->quantity = $this->getTotalAvailableFromSizes($sizes);
        $item->save();

        return $item;
    }

    public function show($id)
    {
        $item = Item::findOrFail($id);

        $availableSizes = $this->getAvailableSizes($item);
        $item->quantity = $this->getTotalAvailableFromSizes($availableSizes);

        return response()->json([
            'product' => $item,
            'availableSizes' => $availableSizes,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:items,id',
            'price' => 'required',
            'size' => 'required|string',
            'delivery' => 'required|in:delivery,pickup',
            'rent_time' => 'required|date',
            'gcash_reference' => 'required|string',
        ]);

        $item = Item::findOrFail($request->product_id);
        $sizes = $this->getAvailableSizes($item);

        $selectedSize = collect($sizes)->first(function ($size) use ($request) {
            return strtolower($size['size'] ?? '') === strtolower($request->size);
        });

        if (!$selectedSize) {
            return response()->json([
                'message' => 'Selected size does not exist.'
            ], 422);
        }

        if ((int) ($selectedSize['available'] ?? 0) <= 0) {
            return response()->json([
                'message' => 'This size is no longer available.'
            ], 422);
        }

        Reservation::create([
            'user_id' => auth()->id(),
            'product_id' => $item->id,
            'size' => strtolower($request->size),
            'rent_time' => $request->rent_time,
            'delivery' => $request->delivery,
            'gcash_reference' => $request->gcash_reference,
            'price' => $request->price,
            'status' => 'pending',
        ]);

        $this->refreshItemQuantity($item);

        return response()->json([
            'message' => 'Reservation submitted successfully!'
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,rented,returned',
        ]);

        $reservation = Reservation::findOrFail($id);
        $reservation->status = $request->status;
        $reservation->save();

        $item = Item::find($reservation->product_id);

        if ($item) {
            $this->refreshItemQuantity($item);
        }

        return response()->json([
            'message' => 'Reservation status updated successfully.'
        ]);
    }
}
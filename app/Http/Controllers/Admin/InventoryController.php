<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Reservation;
use Illuminate\Support\Facades\Storage;

class InventoryController extends Controller
{
    /* ================== HELPERS ================== */

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
        $totalItemQuantity = (int) ($item->quantity ?? 0);

        foreach ($sizes as &$size) {
            $sizeName = strtolower($size['size'] ?? '');

            $usedCount = Reservation::where('product_id', $item->id)
                ->whereRaw('LOWER(size) = ?', [$sizeName])
                ->whereIn('status', $this->getBlockingStatuses())
                ->count();

            if (
                array_key_exists('stock', $size) &&
                $size['stock'] !== null &&
                $size['stock'] !== ''
            ) {
                $stock = (int) $size['stock'];
            } else {
                if (count($sizes) === 1) {
                    $stock = $totalItemQuantity;
                } else {
                    $stock = 1;
                }
            }

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

    private function getRentedCount(Item $item)
    {
        return Reservation::where('product_id', $item->id)
            ->whereIn('status', $this->getBlockingStatuses())
            ->count();
    }

    private function transformItem(Item $item)
    {
        $sizes = $this->getAvailableSizes($item);

        $item->sizes = $sizes;
        $item->quantity = $this->getTotalAvailableFromSizes($sizes);
        $item->rented = $this->getRentedCount($item);

        return $item;
    }

    /* ================== INVENTORY LIST API ================== */

    public function men()
    {
        $items = Item::where('category', 'men_tuxedo')
            ->get()
            ->map(function ($item) {
                return $this->transformItem($item);
            });

        return response()->json($items);
    }

    public function menPS()
    {
        $items = Item::where('category', 'men_prom')
            ->get()
            ->map(function ($item) {
                return $this->transformItem($item);
            });

        return response()->json($items);
    }

    public function women()
    {
        $items = Item::where('category', 'women_wedding')
            ->get()
            ->map(function ($item) {
                return $this->transformItem($item);
            });

        return response()->json($items);
    }

    public function womenPS()
    {
        $items = Item::where('category', 'women_prom')
            ->get()
            ->map(function ($item) {
                return $this->transformItem($item);
            });

        return response()->json($items);
    }

    /* ================== SINGLE ITEM API ================== */

    public function show(Item $item)
    {
        return response()->json($this->transformItem($item));
    }

    /* ================== STORE ITEM ================== */

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_name'  => 'required|string|max:255',
            'quantity'   => 'required|integer|min:0',
            'rental_fee' => 'required|numeric|min:0',
            'category'   => 'required|string',
            'image'      => 'nullable|image|max:2048',
            'sizes'      => 'required|string',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('items', 'public');
        }

        $item = Item::create($validated);

        return response()->json([
            'message' => 'Item added successfully!',
            'item' => $this->transformItem($item)
        ], 201);
    }

    /* ================== UPDATE ITEM ================== */

    public function update(Request $request, Item $item)
    {
        $validated = $request->validate([
            'item_name'  => 'required|string|max:255',
            'quantity'   => 'required|integer|min:0',
            'rental_fee' => 'required|numeric|min:0',
            'image'      => 'nullable|image|max:2048',
            'sizes'      => 'required|string',
        ]);

        $item->item_name = $validated['item_name'];
        $item->quantity = $validated['quantity'];
        $item->rental_fee = $validated['rental_fee'];
        $item->sizes = $validated['sizes'];

        if ($request->hasFile('image')) {
            if ($item->image && Storage::disk('public')->exists($item->image)) {
                Storage::disk('public')->delete($item->image);
            }

            $item->image = $request->file('image')->store('items', 'public');
        }

        $item->save();

        return response()->json([
            'message' => 'Item updated successfully!',
            'item' => $this->transformItem($item)
        ]);
    }

    /* ================== DELETE ITEM ================== */

    public function destroy(Item $item)
    {
        try {
            if ($item->image && Storage::disk('public')->exists($item->image)) {
                Storage::disk('public')->delete($item->image);
            }

            $item->delete();

            return response()->json([
                'message' => 'Item deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Delete failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
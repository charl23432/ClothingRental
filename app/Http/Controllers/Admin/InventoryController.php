<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Item;
use Illuminate\Support\Facades\Storage;

class InventoryController extends Controller
{
    /* ================== INVENTORY LIST API ================== */

    public function men()
    {
        $items = Item::where('category', 'men_tuxedo')->get();
        return response()->json($items);
    }

    public function menPS()
    {
        $items = Item::where('category', 'men_prom')->get();
        return response()->json($items);
    }

    public function women()
    {
        $items = Item::where('category', 'women_wedding')->get();
        return response()->json($items);
    }

    public function womenPS()
    {
        $items = Item::where('category', 'women_prom')->get();
        return response()->json($items);
    }

    /* ================== SINGLE ITEM API ================== */

    public function show(Item $item)
    {
        return response()->json($item);
    }

    /* ================== STORE ITEM ================== */

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_name'  => 'required|string|max:255',
            'quantity'   => 'required|integer|min:1',
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
            'item' => $item
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
            if ($item->image) {
                Storage::disk('public')->delete($item->image);
            }

            $item->image = $request->file('image')->store('items', 'public');
        }

        $item->save();

        return response()->json([
            'message' => 'Item updated successfully!',
            'item' => $item
        ]);
    }

    /* ================== DELETE ITEM ================== */

    public function destroy(Item $item)
    {
        if ($item->image) {
            Storage::disk('public')->delete($item->image);
        }

        $item->delete();

        return response()->json([
            'message' => 'Item deleted successfully!'
        ]);
    }
}
<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Reservation;

class ProductController extends Controller
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

    private function transformItem(Item $item)
    {
        $sizes = $this->getAvailableSizes($item);

        $item->sizes = $sizes;
        $item->quantity = $this->getTotalAvailableFromSizes($sizes);

        return $item;
    }

    public function men()
    {
        $tux = Item::where('category', 'men_tuxedo')
            ->get()
            ->map(function ($item) {
                return $this->transformItem($item);
            });

        $prom = Item::where('category', 'men_prom')
            ->get()
            ->map(function ($item) {
                return $this->transformItem($item);
            });

        return response()->json([
            'tux' => $tux,
            'prom' => $prom,
        ]);
    }

    public function menTuxedo()
    {
        $tuxedo = Item::where('category', 'men_tuxedo')
            ->get()
            ->map(function ($item) {
                return $this->transformItem($item);
            });

        return response()->json($tuxedo);
    }

    public function menProm()
    {
        $prom = Item::where('category', 'men_prom')
            ->get()
            ->map(function ($item) {
                return $this->transformItem($item);
            });

        return response()->json($prom);
    }

    public function women()
    {
        $wedding = Item::where('category', 'women_wedding')
            ->get()
            ->map(function ($item) {
                return $this->transformItem($item);
            });

        $prom = Item::where('category', 'women_prom')
            ->get()
            ->map(function ($item) {
                return $this->transformItem($item);
            });

        return response()->json([
            'wedding' => $wedding,
            'prom' => $prom,
        ]);
    }

    public function womenWedding()
    {
        $wedding = Item::where('category', 'women_wedding')
            ->get()
            ->map(function ($item) {
                return $this->transformItem($item);
            });

        return response()->json($wedding);
    }

    public function womenProm()
    {
        $prom = Item::where('category', 'women_prom')
            ->get()
            ->map(function ($item) {
                return $this->transformItem($item);
            });

        return response()->json($prom);
    }

    public function details($id)
    {
        $product = Item::findOrFail($id);
        $product = $this->transformItem($product);

        return response()->json($product);
    }
}
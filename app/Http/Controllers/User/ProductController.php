<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Item;

class ProductController extends Controller
{
    public function men()
    {
        $tux = Item::where('category', 'men_tuxedo')->get();
        $prom = Item::where('category', 'men_prom')->get();

        return response()->json([
            'tux' => $tux,
            'prom' => $prom,
        ]);
    }

    public function menTuxedo()
    {
        $tuxedo = Item::where('category', 'men_tuxedo')->get();

        return response()->json($tuxedo);
    }

    public function menProm()
    {
        $prom = Item::where('category', 'men_prom')->get();

        return response()->json($prom);
    }

    public function women()
    {
        $wedding = Item::where('category', 'women_wedding')->get();
        $prom    = Item::where('category', 'women_prom')->get();

        return response()->json([
            'wedding' => $wedding,
            'prom' => $prom,
        ]);
    }

    public function womenWedding()
    {
        $wedding = Item::where('category', 'women_wedding')->get();

        return response()->json($wedding);
    }

    public function womenProm()
    {
        $prom = Item::where('category', 'women_prom')->get();

        return response()->json($prom);
    }

    public function details($id)
    {
        $product = Item::findOrFail($id);

        return response()->json($product);
    }
}
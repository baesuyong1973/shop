<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShopController extends Controller
{
    /**
     * List the active shops (storefront directory).
     */
    public function index(): Response
    {
        return Inertia::render('Shops/Index', [
            'shops' => Shop::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    /**
     * Display a single shop's storefront (formerly the site root).
     */
    public function show(Request $request, Shop $shop): Response
    {
        abort_unless($shop->is_active, 404);

        $filters = $request->only(['name']);

        return Inertia::render('Shops/Show', [
            'shop' => $shop,
            'products' => $shop->products()
                ->with('prefecture', 'unit')
                ->where('is_active', true)
                ->filter($filters)
                ->latest()
                ->paginate(20)
                ->withQueryString(),
            'filters' => $filters,
        ]);
    }
}

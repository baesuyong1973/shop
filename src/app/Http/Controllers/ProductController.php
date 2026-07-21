<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Shop;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function show(Shop $shop, Product $product): Response
    {
        abort_unless($product->is_active, 404);

        return Inertia::render('Products/Show', [
            'shop' => $shop,
            'product' => $product->load('prefecture', 'unit'),
        ]);
    }
}

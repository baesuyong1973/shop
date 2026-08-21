<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StaticPageController extends Controller
{
    /**
     * Render a footer static page (how-to-use, privacy, contact, company).
     */
    public function show(Request $request, string $slug): Response
    {
        return Inertia::render('StaticPages/Show', [
            'slug' => $slug,
        ]);
    }
}

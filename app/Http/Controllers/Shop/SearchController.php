<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function suggest(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json(['products' => [], 'categories' => []]);
        }

        $products = Product::where('is_active', true)
            ->where('name', 'like', "%{$q}%")
            ->with('category')
            ->take(6)
            ->get()
            ->map(fn (Product $p) => [
                'name' => $p->name,
                'url' => route('products.show', $p->slug),
                'price' => format_price($p->price),
                'image' => $p->thumbnailUrl(),
                'category' => $p->category->name,
            ]);

        $categories = Category::where('is_active', true)
            ->where('name', 'like', "%{$q}%")
            ->take(4)
            ->get()
            ->map(fn (Category $c) => [
                'name' => $c->name,
                'url' => route('products.index', ['category' => $c->slug]),
            ]);

        return response()->json([
            'products' => $products,
            'categories' => $categories,
        ]);
    }
}

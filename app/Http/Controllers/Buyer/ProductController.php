<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Public product listing.
     */
    public function index(Request $request): View
    {
        $query = Product::with('farmer', 'category')
            ->where('status', 'active')
            ->where('stock_quantity', '>', 0);
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        if ($request->query('category')) {
            $query->whereHas('category', fn($q) => $q->where('slug', $request->query('category')));
        }

        if ($request->query('search')) {
            $query->where('name', 'like', '%' . $request->query('search') . '%');
        }

        $products = $query->latest()->paginate(20)->withQueryString();
        $categories = ProductCategory::all();

        return view('buyer.products.index', compact('products', 'categories'));
    }

    /**
     * Public product detail.
     */
    public function show(string $slug): View
    {
        $product = Product::with(['farmer', 'category', 'images'])
            ->where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        return view('buyer.products.show', compact('product'));
    }
}

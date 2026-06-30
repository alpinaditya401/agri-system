<?php

namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        $products = Product::with('category')
            ->where('farmer_id', Auth::id())
            ->latest()
            ->paginate(15);

        return view('farmer.products.index', compact('products'));
    }

    public function create(): View
    {
        $categories = ProductCategory::all();
        return view('farmer.products.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'description'    => ['nullable', 'string'],
            'price_per_unit' => ['required', 'numeric', 'min:0'],
            'unit'           => ['required', 'string', 'max:20'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'minimum_order'  => ['required', 'integer', 'min:1'],
            'category_id'    => ['nullable', 'exists:product_categories,id'],
            'main_image'     => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'origin_province'=> ['nullable', 'string', 'max:100'],
            'origin_district'=> ['nullable', 'string', 'max:100'],
            'origin_lat'     => ['nullable', 'numeric'],
            'origin_lng'     => ['nullable', 'numeric'],
        ]);

        if ($request->hasFile('main_image')) {
            $validated['main_image'] = $request->file('main_image')->store('products', 'public');
        } else {
            unset($validated['main_image']);
        }

        $slug = Str::slug($validated['name']);
        $originalSlug = $slug;
        $count = 1;
        while (Product::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        Product::create([
            'farmer_id'       => Auth::id(),
            'slug'            => $slug,
            'status'          => 'draft',
            ...$validated,
        ]);

        return redirect()->route('farmer.produk.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function show(Product $produk): View
    {
        $this->authorizeFarmerProduct($produk);
        return view('farmer.products.show', compact('produk'));
    }

    public function edit(Product $produk): View
    {
        $this->authorizeFarmerProduct($produk);
        $categories = ProductCategory::all();
        return view('farmer.products.edit', compact('produk', 'categories'));
    }

    public function update(Request $request, Product $produk): RedirectResponse
    {
        $this->authorizeFarmerProduct($produk);

        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'description'    => ['nullable', 'string'],
            'price_per_unit' => ['required', 'numeric', 'min:0'],
            'unit'           => ['required', 'string', 'max:20'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'minimum_order'  => ['required', 'integer', 'min:1'],
            'category_id'    => ['nullable', 'exists:product_categories,id'],
            'main_image'     => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'origin_province'=> ['nullable', 'string', 'max:100'],
            'origin_district'=> ['nullable', 'string', 'max:100'],
            'origin_lat'     => ['nullable', 'numeric'],
            'origin_lng'     => ['nullable', 'numeric'],
        ]);

        if ($request->hasFile('main_image')) {
            $this->deleteStoredMainImage($produk->main_image);
            $validated['main_image'] = $request->file('main_image')->store('products', 'public');
        } else {
            unset($validated['main_image']);
        }

        $produk->update($validated);

        return redirect()->route('farmer.produk.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $produk): RedirectResponse
    {
        $this->authorizeFarmerProduct($produk);
        $produk->delete();

        return redirect()->route('farmer.produk.index')->with('success', 'Produk berhasil dihapus.');
    }

    public function toggleStatus(Product $produk): RedirectResponse
    {
        $this->authorizeFarmerProduct($produk);

        $newStatus = $produk->status === 'active' ? 'inactive' : 'active';
        $produk->update(['status' => $newStatus]);

        return back()->with('success', "Status produk diubah menjadi {$newStatus}.");
    }

    private function authorizeFarmerProduct(Product $product): void
    {
        if ($product->farmer_id !== Auth::id()) {
            abort(403, 'Akses ditolak. Produk ini bukan milik Anda.');
        }
    }

    private function deleteStoredMainImage(?string $path): void
    {
        if (!$path || Str::startsWith($path, ['http://', 'https://', '/', 'storage/', 'images/'])) {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}

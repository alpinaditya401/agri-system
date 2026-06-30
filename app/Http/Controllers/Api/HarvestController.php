<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHarvestRequest;
use App\Http\Resources\HarvestResource;
use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class HarvestController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'farmer']);

        if ($request->filled('commodity')) {
            $query->where('name', 'like', '%' . $request->commodity . '%');
        }

        if ($request->filled('location')) {
            $query->where(function ($q) use ($request) {
                $q->where('origin_district', 'like', '%' . $request->location . '%')
                    ->orWhere('origin_province', 'like', '%' . $request->location . '%');
            });
        }

        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category)
                    ->orWhere('name', 'like', '%' . $request->category . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $perPage = $request->integer('per_page', 10);

        $products = $query
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return HarvestResource::collection($products);
    }

    public function store(StoreHarvestRequest $request)
    {
        if (!Auth::user()->isFarmer()) {
            return response()->json([
                'message' => 'Hanya petani yang dapat menambahkan data hasil panen.',
            ], 403);
        }

        $validated = $request->validated();

        $slug = Str::slug($validated['name']);
        $originalSlug = $slug;
        $count = 1;

        while (Product::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        $product = Product::create([
            'farmer_id' => Auth::id(),
            'slug' => $slug,
            'status' => $validated['status'] ?? 'draft',
            ...$validated,
        ]);

        $product->load(['category', 'farmer']);

        return (new HarvestResource($product))
            ->additional([
                'message' => 'Data hasil panen berhasil ditambahkan',
            ])
            ->response()
            ->setStatusCode(201);
    }

    public function show($id)
    {
        try {
            $product = Product::with(['category', 'farmer'])->findOrFail($id);

            return new HarvestResource($product);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Resource tidak ditemukan',
                'message' => 'Data hasil panen dengan ID ' . $id . ' tidak ditemukan.',
            ], 404);
        }
    }

    public function update(StoreHarvestRequest $request, $id)
    {
        try {
            $product = Product::findOrFail($id);

            if (!Auth::user()->isAdminPanelUser() && $product->farmer_id !== Auth::id()) {
                return response()->json([
                    'message' => 'Anda tidak memiliki akses untuk mengubah data hasil panen ini.',
                ], 403);
            }

            $validated = $request->validated();

            if (($validated['name'] ?? $product->name) !== $product->name) {
                $slug = Str::slug($validated['name']);
                $originalSlug = $slug;
                $count = 1;

                while (Product::where('slug', $slug)->where('id', '!=', $product->id)->exists()) {
                    $slug = $originalSlug . '-' . $count++;
                }

                $validated['slug'] = $slug;
            }

            $product->update($validated);
            $product->load(['category', 'farmer']);

            return (new HarvestResource($product))
                ->additional([
                    'message' => 'Data hasil panen berhasil diperbarui',
                ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Resource tidak ditemukan',
                'message' => 'Data hasil panen dengan ID ' . $id . ' tidak ditemukan.',
            ], 404);
        }
    }

    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);

            if (!Auth::user()->isAdminPanelUser() && $product->farmer_id !== Auth::id()) {
                return response()->json([
                    'message' => 'Anda tidak memiliki akses untuk menghapus data hasil panen ini.',
                ], 403);
            }

            $product->delete();

            return response()->json([
                'message' => 'Data hasil panen berhasil dihapus',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Resource tidak ditemukan',
                'message' => 'Data hasil panen dengan ID ' . $id . ' tidak ditemukan.',
            ], 404);
        }
    }
}
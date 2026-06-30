<?php

namespace App\Http\Controllers\Distributor;

use App\Http\Controllers\Controller;
use App\Models\FertilizerStock;
use App\Models\FertilizerType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StockController extends Controller
{
    public function index(): View
    {
        $stocks = FertilizerStock::with('fertilizerType')
            ->where('distributor_id', Auth::id())
            ->latest()
            ->paginate(15);

        $types = FertilizerType::where('is_active', true)->get();

        return view('distributor.stock.index', compact('stocks', 'types'));
    }

    public function addStock(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'fertilizer_type_id' => ['required', 'exists:fertilizer_types,id'],
            'stock_kg'           => ['required', 'integer', 'min:1', 'max:100000'],
            'batch_number'       => ['nullable', 'string', 'max:100'],
            'received_date'      => ['required', 'date'],
            'expiry_date'        => ['nullable', 'date', 'after:received_date'],
        ]);

        $existing = FertilizerStock::where('distributor_id', Auth::id())
            ->where('fertilizer_type_id', $validated['fertilizer_type_id'])
            ->where('batch_number', $validated['batch_number'])
            ->first();

        if ($existing) {
            $existing->increment('stock_kg', $validated['stock_kg']);
        } else {
            FertilizerStock::create([
                'distributor_id'     => Auth::id(),
                'fertilizer_type_id' => $validated['fertilizer_type_id'],
                'stock_kg'           => $validated['stock_kg'],
                'batch_number'       => $validated['batch_number'],
                'received_date'      => $validated['received_date'],
                'expiry_date'        => $validated['expiry_date'],
            ]);
        }

        return back()->with('success', 'Stok pupuk berhasil ditambahkan.');
    }

    public function history(): View
    {
        $stocks = FertilizerStock::with('fertilizerType')
            ->where('distributor_id', Auth::id())
            ->orderBy('received_date', 'desc')
            ->paginate(20);

        return view('distributor.stock.history', compact('stocks'));
    }
}

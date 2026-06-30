<?php

namespace App\Http\Controllers;

use App\Models\FertilizerStock;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        return view('notifications.index');
    }

    /**
     * JSON list of notifications + unread count for the authenticated user.
     */
    public function fetch(): JsonResponse
    {
        $list = Notification::where('user_id', auth()->id())
            ->latest()
            ->limit(50)
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'list'   => $list,
                'unread' => $list->where('dibaca', false)->count(),
            ],
        ]);
    }

    /**
     * Bell-icon summary used by the topbar dropdown (small payload).
     */
    public function summary(): JsonResponse
    {
        $unread = Notification::where('user_id', auth()->id())->unread()->count();
        $recent = Notification::where('user_id', auth()->id())->latest()->limit(5)->get();

        return response()->json(['status' => 'success', 'unread' => $unread, 'recent' => $recent]);
    }

    public function markRead(Request $request): JsonResponse
    {
        $request->validate(['id' => ['required', 'exists:notifications,id']]);

        Notification::where('id', $request->input('id'))
            ->where('user_id', auth()->id())
            ->update(['dibaca' => true, 'read_at' => now()]);

        return response()->json(['status' => 'success']);
    }

    public function markAllRead(): JsonResponse
    {
        Notification::where('user_id', auth()->id())
            ->where('dibaca', false)
            ->update(['dibaca' => true, 'read_at' => now()]);

        return response()->json(['status' => 'success']);
    }

    /**
     * Distributor stock alerts (low stock banner) — reused on notifications page.
     */
    public function stockAlerts(): JsonResponse
    {
        $user = auth()->user();

        if (!$user->isDistributor()) {
            return response()->json(['status' => 'success', 'data' => []]);
        }

        $threshold = 150;

        $alerts = FertilizerStock::with('fertilizerType')
            ->where('distributor_id', $user->id)
            ->get()
            ->filter(fn($s) => ($s->stock_kg - $s->reserved_kg) < $threshold)
            ->map(fn($s) => [
                'nama'          => $s->fertilizerType?->name,
                'stok_saat_ini' => max(0, $s->stock_kg - $s->reserved_kg),
                'stok_min'      => $threshold,
            ])
            ->values();

        return response()->json(['status' => 'success', 'data' => $alerts]);
    }
}

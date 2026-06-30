<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FertilizerTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class FertilizerTrackingController extends Controller
{
    public function show(FertilizerTransaction $transaction): JsonResponse
    {
        $this->authorizeTrackingView($transaction);

        $transaction->load(['farmer', 'distributor', 'fertilizerType']);

        return response()->json([
            'status' => 'success',
            'data' => $this->trackingPayload($transaction),
        ]);
    }

    public function update(Request $request, FertilizerTransaction $transaction): JsonResponse
    {
        abort_unless($transaction->distributor_id === Auth::id(), 403, 'Hanya distributor terkait yang dapat mengirim lokasi tracking.');

        if (! in_array($transaction->status, ['approved', 'dispensed'], true)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Live tracking hanya tersedia setelah permintaan disetujui.',
            ], 422);
        }

        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy' => ['nullable', 'numeric', 'min:0', 'max:99999'],
            'tracking_status' => ['nullable', Rule::in(['on_the_way', 'nearby', 'arrived', 'paused'])],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $transaction->update([
            'tracking_status' => $validated['tracking_status'] ?? $transaction->tracking_status ?? 'on_the_way',
            'tracking_latitude' => $validated['latitude'],
            'tracking_longitude' => $validated['longitude'],
            'tracking_accuracy' => $validated['accuracy'] ?? null,
            'tracking_note' => $validated['note'] ?? $transaction->tracking_note,
            'tracking_started_at' => $transaction->tracking_started_at ?? now(),
            'tracking_updated_at' => now(),
        ]);

        $transaction->load(['farmer', 'distributor', 'fertilizerType']);

        return response()->json([
            'status' => 'success',
            'message' => 'Lokasi distributor berhasil diperbarui.',
            'data' => $this->trackingPayload($transaction),
        ]);
    }

    private function authorizeTrackingView(FertilizerTransaction $transaction): void
    {
        $user = Auth::user();

        abort_unless(
            $user && (
                $transaction->farmer_id === $user->id ||
                $transaction->distributor_id === $user->id ||
                $user->isAdminPanelUser()
            ),
            403,
            'Akses tracking ditolak.'
        );
    }

    private function trackingPayload(FertilizerTransaction $transaction): array
    {
        return [
            'transaction' => [
                'id' => $transaction->id,
                'number' => $transaction->transaction_number,
                'status' => $transaction->status,
                'fertilizer' => $transaction->fertilizerType?->name,
                'approved_kg' => $transaction->approved_kg,
                'requested_kg' => $transaction->requested_kg,
            ],
            'tracking' => [
                'has_location' => filled($transaction->tracking_latitude) && filled($transaction->tracking_longitude),
                'status' => $transaction->tracking_status,
                'status_label' => $this->trackingStatusLabel($transaction->tracking_status),
                'latitude' => $transaction->tracking_latitude ? (float) $transaction->tracking_latitude : null,
                'longitude' => $transaction->tracking_longitude ? (float) $transaction->tracking_longitude : null,
                'accuracy' => $transaction->tracking_accuracy ? (float) $transaction->tracking_accuracy : null,
                'note' => $transaction->tracking_note,
                'started_at' => $transaction->tracking_started_at?->toISOString(),
                'updated_at' => $transaction->tracking_updated_at?->toISOString(),
                'updated_human' => $transaction->tracking_updated_at?->diffForHumans(),
            ],
            'distributor' => [
                'id' => $transaction->distributor?->id,
                'name' => $transaction->distributor?->name,
                'phone' => $transaction->distributor?->phone,
                'latitude' => $transaction->distributor?->latitude ? (float) $transaction->distributor->latitude : null,
                'longitude' => $transaction->distributor?->longitude ? (float) $transaction->distributor->longitude : null,
            ],
            'farmer' => [
                'id' => $transaction->farmer?->id,
                'name' => $transaction->farmer?->name,
                'phone' => $transaction->farmer?->phone,
                'address' => $transaction->farmer?->address,
                'district' => $transaction->farmer?->district,
                'latitude' => $transaction->farmer?->latitude ? (float) $transaction->farmer->latitude : null,
                'longitude' => $transaction->farmer?->longitude ? (float) $transaction->farmer->longitude : null,
            ],
        ];
    }

    private function trackingStatusLabel(?string $status): string
    {
        return match ($status) {
            'on_the_way' => 'Dalam Perjalanan',
            'nearby' => 'Mendekati Lokasi',
            'arrived' => 'Sampai Lokasi',
            'paused' => 'Tracking Dijeda',
            default => 'Belum Aktif',
        };
    }
}

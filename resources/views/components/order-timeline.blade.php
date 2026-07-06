@props([
    'order',
])

@php
    $status = $order->order_status;
    $paid = $order->payment_status === 'paid';
    $isCancelled = $status === 'cancelled';

    $steps = [
        [
            'label' => 'Menunggu Pembayaran',
            'description' => 'Invoice dibuat dan menunggu pembayaran pembeli.',
            'done' => $paid || $isCancelled,
            'active' => ! $paid && ! $isCancelled,
            'time' => $order->created_at,
        ],
        [
            'label' => 'Dibayar',
            'description' => 'Pembayaran diterima sistem.',
            'done' => $paid,
            'active' => $paid && $status === 'pending',
            'time' => $order->paid_at,
        ],
        [
            'label' => 'Diproses Petani',
            'description' => 'Pesanan dikonfirmasi atau sedang disiapkan petani.',
            'done' => in_array($status, ['confirmed', 'processing', 'shipped', 'delivered', 'completed'], true),
            'active' => in_array($status, ['confirmed', 'processing'], true),
            'time' => null,
        ],
        [
            'label' => 'Dikirim',
            'description' => $order->tracking_number ? 'Nomor resi: ' . $order->tracking_number : 'Pesanan sedang dalam pengiriman.',
            'done' => in_array($status, ['shipped', 'delivered', 'completed'], true),
            'active' => $status === 'shipped',
            'time' => $order->shipped_at,
        ],
        [
            'label' => 'Diterima',
            'description' => 'Pesanan sudah diterima pembeli.',
            'done' => in_array($status, ['delivered', 'completed'], true),
            'active' => $status === 'delivered',
            'time' => $order->delivered_at,
        ],
        [
            'label' => 'Selesai',
            'description' => 'Transaksi sudah selesai.',
            'done' => $status === 'completed',
            'active' => $status === 'completed',
            'time' => $status === 'completed' ? ($order->delivered_at ?? $order->updated_at) : null,
        ],
    ];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-3xl border border-slate-200 bg-white p-5 shadow-sm']) }}>
    <div class="flex items-start justify-between gap-4">
        <div>
            <p class="ag-label">Tracking Pesanan</p>
            <h2 class="mt-2 text-xl font-black text-slate-950">Progress transaksi</h2>
        </div>
        <x-ui.badge :tone="$order->order_status">{{ $order->order_status_label }}</x-ui.badge>
    </div>

    @if ($isCancelled)
        <div class="mt-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
            Pesanan ini dibatalkan. Timeline berhenti pada status pembatalan.
        </div>
    @endif

    <div class="mt-5 space-y-4">
        @foreach ($steps as $step)
            @php
                $stateClass = $step['done']
                    ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                    : ($step['active'] ? 'border-amber-200 bg-amber-50 text-amber-700' : 'border-slate-200 bg-slate-50 text-slate-400');
                $dotClass = $step['done']
                    ? 'bg-emerald-600'
                    : ($step['active'] ? 'bg-amber-500' : 'bg-slate-300');
            @endphp
            <div class="flex gap-3">
                <div class="flex flex-col items-center">
                    <span class="mt-1 h-3 w-3 rounded-full {{ $dotClass }}"></span>
                    @if (! $loop->last)
                        <span class="mt-1 h-full min-h-8 w-px bg-slate-200"></span>
                    @endif
                </div>
                <div class="flex-1 rounded-2xl border px-4 py-3 {{ $stateClass }}">
                    <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                        <p class="font-black">{{ $step['label'] }}</p>
                        @if ($step['time'])
                            <p class="text-xs font-semibold opacity-75">{{ $step['time']->translatedFormat('d M Y, H:i') }}</p>
                        @endif
                    </div>
                    <p class="mt-1 text-xs font-semibold leading-5 opacity-80">{{ $step['description'] }}</p>
                </div>
            </div>
        @endforeach
    </div>
</div>

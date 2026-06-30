<x-layouts.app :title="'Payment Gateway Settings - Agrilink'">
    <x-slot:sidebar>
        @include('admin._sidebar')
    </x-slot:sidebar>

    <x-slot:header>
        <h1 class="text-lg font-bold text-gray-800">Payment Gateway</h1>
        <p class="text-gray-500 text-sm">Kelola kredensial pembayaran tanpa mengubah file .env.</p>
    </x-slot:header>

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-[minmax(0,1fr)_340px]">
        <form method="POST" action="{{ route('admin-master.payment-settings.update') }}" class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            @csrf
            @method('PATCH')

            @if ($errors->any())
                <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div class="space-y-5">
                <div>
                    <p class="text-sm font-bold text-gray-900">Gateway Aktif</p>
                    <p class="mt-1 text-xs text-gray-500">Pilih Demo Auto-Paid untuk demo/hosting tugas tanpa akun payment gateway.</p>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <label class="block">
                        <span class="text-xs font-semibold text-gray-500">Payment Gateway</span>
                        <select name="payment_gateway" class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm outline-none focus:border-emerald-400">
                            <option value="demo" {{ old('payment_gateway', $settings['payment_gateway']) === 'demo' ? 'selected' : '' }}>Demo Auto-Paid</option>
                            <option value="midtrans" {{ old('payment_gateway', $settings['payment_gateway']) === 'midtrans' ? 'selected' : '' }}>Midtrans Snap</option>
                        </select>
                    </label>

                    <label class="block">
                        <span class="text-xs font-semibold text-gray-500">Webhook Signature</span>
                        <select name="payment_webhook_gateway" class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm outline-none focus:border-emerald-400">
                            @foreach (['midtrans' => 'Midtrans', 'xendit' => 'Xendit', 'auto' => 'Auto Detect'] as $value => $label)
                                <option value="{{ $value }}" {{ old('payment_webhook_gateway', $settings['payment_webhook_gateway']) === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>

                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                    <p class="font-bold">Mode Demo Auto-Paid</p>
                    <p class="mt-1 text-xs leading-5">Checkout langsung dianggap lunas, stok langsung berkurang, dan order masuk ke petani. Mode ini cocok untuk demo aplikasi, bukan untuk transaksi uang asli.</p>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-bold text-gray-900">Midtrans</p>
                            <p class="mt-1 text-xs text-gray-500">Isi key dari dashboard Midtrans Sandbox/Production.</p>
                        </div>
                        <label class="inline-flex items-center gap-2 text-xs font-bold text-gray-600">
                            <input type="checkbox" name="midtrans_is_production" value="1" {{ old('midtrans_is_production', $settings['midtrans_is_production']) ? 'checked' : '' }} class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                            Production
                        </label>
                    </div>

                    <div class="mt-4 space-y-4">
                        <label class="block">
                            <span class="text-xs font-semibold text-gray-500">Server Key</span>
                            <input type="password" name="midtrans_server_key" placeholder="{{ $settings['midtrans_server_key_saved'] ? 'Server key sudah tersimpan. Isi hanya jika ingin mengganti.' : 'SB-Mid-server-...' }}" class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm outline-none focus:border-emerald-400">
                        </label>

                        <label class="block">
                            <span class="text-xs font-semibold text-gray-500">Client Key</span>
                            <input type="password" name="midtrans_client_key" placeholder="{{ $settings['midtrans_client_key_saved'] ? 'Client key sudah tersimpan. Isi hanya jika ingin mengganti.' : 'SB-Mid-client-...' }}" class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm outline-none focus:border-emerald-400">
                        </label>

                        <label class="block">
                            <span class="text-xs font-semibold text-gray-500">Snap URL</span>
                            <input type="url" name="midtrans_snap_url" value="{{ old('midtrans_snap_url', $settings['midtrans_snap_url']) }}" required class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm outline-none focus:border-emerald-400">
                        </label>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
                    <p class="text-sm font-bold text-gray-900">Xendit Webhook</p>
                    <p class="mt-1 text-xs text-gray-500">Opsional. Dipakai kalau webhook gateway diset ke Xendit atau Auto Detect.</p>
                    <label class="mt-4 block">
                        <span class="text-xs font-semibold text-gray-500">Callback Token</span>
                        <input type="password" name="xendit_callback_token" placeholder="{{ $settings['xendit_callback_token_saved'] ? 'Callback token sudah tersimpan. Isi hanya jika ingin mengganti.' : 'x-callback-token' }}" class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm outline-none focus:border-emerald-400">
                    </label>
                </div>

                <div class="flex flex-wrap gap-3 pt-2">
                    <button type="submit" class="rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-bold text-white transition-colors hover:bg-emerald-700">
                        Simpan Pengaturan
                    </button>
                    <a href="{{ route('admin-master.dashboard') }}" class="rounded-xl bg-gray-100 px-5 py-2.5 text-sm font-bold text-gray-600 transition-colors hover:bg-gray-200">
                        Kembali
                    </a>
                </div>
            </div>
        </form>

        <aside class="self-start rounded-2xl border border-emerald-100 bg-emerald-50 p-5">
            <p class="text-sm font-bold text-emerald-900">Status</p>
            <div class="mt-4 space-y-3 text-sm">
                <div class="flex items-center justify-between rounded-xl bg-white px-3 py-2">
                    <span class="text-gray-600">Gateway Aktif</span>
                    <span class="font-bold text-gray-800">
                        {{ $settings['payment_gateway'] === 'demo' ? 'Demo Auto-Paid' : 'Midtrans' }}
                    </span>
                </div>
                <div class="flex items-center justify-between rounded-xl bg-white px-3 py-2">
                    <span class="text-gray-600">Midtrans Server Key</span>
                    <span class="font-bold {{ $settings['midtrans_server_key_saved'] ? 'text-emerald-700' : 'text-red-600' }}">
                        {{ $settings['midtrans_server_key_saved'] ? 'Tersimpan' : 'Kosong' }}
                    </span>
                </div>
                <div class="flex items-center justify-between rounded-xl bg-white px-3 py-2">
                    <span class="text-gray-600">Midtrans Client Key</span>
                    <span class="font-bold {{ $settings['midtrans_client_key_saved'] ? 'text-emerald-700' : 'text-gray-500' }}">
                        {{ $settings['midtrans_client_key_saved'] ? 'Tersimpan' : 'Kosong' }}
                    </span>
                </div>
                <div class="flex items-center justify-between rounded-xl bg-white px-3 py-2">
                    <span class="text-gray-600">Mode</span>
                    <span class="font-bold text-gray-800">{{ $settings['midtrans_is_production'] ? 'Production' : 'Sandbox' }}</span>
                </div>
            </div>
            <p class="mt-4 text-xs leading-5 text-emerald-900/75">
                Secret tidak ditampilkan ulang di form. Kalau ingin mengganti, isi key baru lalu simpan.
            </p>
        </aside>
    </div>
</x-layouts.app>

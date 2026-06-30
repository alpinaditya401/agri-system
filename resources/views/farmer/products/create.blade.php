<x-layouts.app :title="'Tambah Produk – Agrilink'">
    <x-slot:sidebar>
        @include('farmer._sidebar')
    </x-slot:sidebar>

    <x-slot:header>
        <h1 class="text-lg font-bold text-gray-800">Tambah Produk Baru</h1>
        <p class="text-gray-500 text-sm">Lengkapi informasi produk yang akan dijual.</p>
    </x-slot:header>

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-[minmax(0,1fr)_300px]">
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <div class="mb-5 border-b border-gray-100 pb-4">
                <p class="text-sm font-bold text-gray-900">Informasi produk</p>
                <p class="mt-1 text-xs text-gray-500">Isi data yang paling membantu pembeli membandingkan harga, stok, dan lokasi asal.</p>
            </div>
            <form method="POST" action="{{ route('farmer.produk.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="text-xs font-semibold text-gray-500">Nama Produk</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full mt-1 px-3 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:border-emerald-400">
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500">Deskripsi</label>
                <textarea name="description" rows="3" class="w-full mt-1 px-3 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:border-emerald-400">{{ old('description') }}</textarea>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500">Foto Produk Utama</label>
                <input type="file" name="main_image" accept="image/jpeg,image/png,image/webp"
                       class="w-full mt-1 px-3 py-2.5 border border-gray-200 rounded-xl text-sm outline-none file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-50 file:px-3 file:py-1.5 file:text-xs file:font-bold file:text-emerald-700 focus:border-emerald-400">
                <p class="mt-1 text-[11px] text-gray-400">Opsional. Format JPG, PNG, atau WebP. Maksimal 4 MB.</p>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-semibold text-gray-500">Harga per Unit (Rp)</label>
                    <input type="number" step="0.01" name="price_per_unit" value="{{ old('price_per_unit') }}" required
                           class="w-full mt-1 px-3 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:border-emerald-400">
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500">Satuan</label>
                    <input type="text" name="unit" value="{{ old('unit', 'kg') }}" required placeholder="kg, ton, ikat"
                           class="w-full mt-1 px-3 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:border-emerald-400">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-semibold text-gray-500">Stok</label>
                    <input type="number" name="stock_quantity" value="{{ old('stock_quantity', 0) }}" required
                           class="w-full mt-1 px-3 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:border-emerald-400">
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500">Minimum Order</label>
                    <input type="number" name="minimum_order" value="{{ old('minimum_order', 1) }}" required
                           class="w-full mt-1 px-3 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:border-emerald-400">
                </div>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500">Kategori</label>
                <select name="category_id" class="w-full mt-1 px-3 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:border-emerald-400">
                    <option value="">Pilih Kategori</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-semibold text-gray-500">Provinsi Asal</label>
                    <input type="text" name="origin_province" value="{{ old('origin_province', auth()->user()->province) }}"
                           class="w-full mt-1 px-3 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:border-emerald-400">
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500">Kabupaten/Kota Asal</label>
                    <input type="text" name="origin_district" value="{{ old('origin_district', auth()->user()->district) }}"
                           class="w-full mt-1 px-3 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:border-emerald-400">
                </div>
            </div>
            <input type="hidden" name="origin_lat" value="{{ old('origin_lat', auth()->user()->latitude) }}">
            <input type="hidden" name="origin_lng" value="{{ old('origin_lng', auth()->user()->longitude) }}">

            <div class="flex gap-3 pt-2">
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-5 py-2.5 rounded-xl text-sm transition-colors">Simpan Produk</button>
                <a href="{{ route('farmer.produk.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 font-semibold px-5 py-2.5 rounded-xl text-sm transition-colors">Batal</a>
            </div>
            </form>
        </div>

        <aside class="self-start rounded-2xl border border-emerald-100 bg-emerald-50 p-5">
            <p class="text-sm font-bold text-emerald-900">Tips listing</p>
            <div class="mt-4 space-y-3 text-sm text-emerald-900/75">
                <p>Gunakan nama spesifik seperti Beras Medium Karawang, bukan hanya Beras.</p>
                <p>Pastikan stok dan minimum order sesuai kondisi lapangan.</p>
                <p>Produk baru disimpan sebagai draft. Aktifkan dari daftar produk saat sudah siap dijual.</p>
            </div>
        </aside>
    </div>
</x-layouts.app>

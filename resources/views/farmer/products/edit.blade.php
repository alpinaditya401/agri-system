<x-layouts.app :title="'Edit Produk – Agrilink'">
    <x-slot:sidebar>
        @include('farmer._sidebar')
    </x-slot:sidebar>

    <x-slot:header>
        <h1 class="text-lg font-bold text-gray-800">Edit Produk</h1>
        <p class="text-gray-500 text-sm">Perbarui informasi produk "{{ $produk->name }}".</p>
    </x-slot:header>

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-[minmax(0,1fr)_300px]">
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <div class="mb-5 border-b border-gray-100 pb-4">
                <p class="text-sm font-bold text-gray-900">Perbarui listing</p>
                <p class="mt-1 text-xs text-gray-500">Jaga data harga, stok, dan asal produk tetap akurat untuk pembeli.</p>
            </div>
            <form method="POST" action="{{ route('farmer.produk.update', $produk) }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="text-xs font-semibold text-gray-500">Nama Produk</label>
                <input type="text" name="name" value="{{ old('name', $produk->name) }}" required
                       class="w-full mt-1 px-3 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:border-emerald-400">
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500">Deskripsi</label>
                <textarea name="description" rows="3" class="w-full mt-1 px-3 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:border-emerald-400">{{ old('description', $produk->description) }}</textarea>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500">Foto Produk Utama</label>
                @if ($produk->main_image_url)
                    <div class="mt-2 flex items-center gap-3 rounded-2xl border border-gray-100 bg-gray-50 p-3">
                        <img src="{{ $produk->main_image_url }}" alt="{{ $produk->name }}" class="h-16 w-16 rounded-xl object-cover">
                        <div>
                            <p class="text-xs font-semibold text-gray-700">Foto saat ini</p>
                            <p class="mt-1 text-[11px] text-gray-400">Upload foto baru hanya jika ingin mengganti.</p>
                        </div>
                    </div>
                @endif
                <input type="file" name="main_image" accept="image/jpeg,image/png,image/webp"
                       class="w-full mt-2 px-3 py-2.5 border border-gray-200 rounded-xl text-sm outline-none file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-50 file:px-3 file:py-1.5 file:text-xs file:font-bold file:text-emerald-700 focus:border-emerald-400">
                <p class="mt-1 text-[11px] text-gray-400">Format JPG, PNG, atau WebP. Maksimal 4 MB.</p>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-semibold text-gray-500">Harga per Unit (Rp)</label>
                    <input type="number" step="0.01" name="price_per_unit" value="{{ old('price_per_unit', $produk->price_per_unit) }}" required
                           class="w-full mt-1 px-3 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:border-emerald-400">
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500">Satuan</label>
                    <input type="text" name="unit" value="{{ old('unit', $produk->unit) }}" required
                           class="w-full mt-1 px-3 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:border-emerald-400">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-semibold text-gray-500">Stok</label>
                    <input type="number" name="stock_quantity" value="{{ old('stock_quantity', $produk->stock_quantity) }}" required
                           class="w-full mt-1 px-3 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:border-emerald-400">
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500">Minimum Order</label>
                    <input type="number" name="minimum_order" value="{{ old('minimum_order', $produk->minimum_order) }}" required
                           class="w-full mt-1 px-3 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:border-emerald-400">
                </div>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500">Kategori</label>
                <select name="category_id" class="w-full mt-1 px-3 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:border-emerald-400">
                    <option value="">Pilih Kategori</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id', $produk->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-semibold text-gray-500">Provinsi Asal</label>
                    <input type="text" name="origin_province" value="{{ old('origin_province', $produk->origin_province) }}"
                           class="w-full mt-1 px-3 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:border-emerald-400">
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500">Kabupaten/Kota Asal</label>
                    <input type="text" name="origin_district" value="{{ old('origin_district', $produk->origin_district) }}"
                           class="w-full mt-1 px-3 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:border-emerald-400">
                </div>
            </div>
            <input type="hidden" name="origin_lat" value="{{ old('origin_lat', $produk->origin_lat) }}">
            <input type="hidden" name="origin_lng" value="{{ old('origin_lng', $produk->origin_lng) }}">

            <div class="flex gap-3 pt-2">
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-5 py-2.5 rounded-xl text-sm transition-colors">Simpan Perubahan</button>
                <a href="{{ route('farmer.produk.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 font-semibold px-5 py-2.5 rounded-xl text-sm transition-colors">Batal</a>
            </div>
            </form>
        </div>

        <aside class="self-start rounded-2xl border border-emerald-100 bg-emerald-50 p-5">
            <p class="text-sm font-bold text-emerald-900">Status listing</p>
            <div class="mt-4 rounded-xl bg-white p-4">
                <p class="text-xs text-gray-400">Produk</p>
                <p class="mt-1 font-bold text-gray-900">{{ $produk->name }}</p>
                <p class="mt-3 text-xs text-gray-400">Status saat ini</p>
                <span class="mt-1 inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $produk->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">{{ ucfirst($produk->status) }}</span>
            </div>
            <p class="mt-4 text-sm text-emerald-900/75">Perubahan harga dan stok langsung memengaruhi tampilan produk di marketplace.</p>
        </aside>
    </div>
</x-layouts.app>

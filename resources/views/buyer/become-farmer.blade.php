<x-layouts.app :title="'Daftar Jadi Penjual - Agrilink'">
    <x-slot:sidebar>
        @include('buyer._sidebar')
    </x-slot:sidebar>

    <x-slot:header>
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h1 class="ag-heading">Daftar Jadi Penjual</h1>
                <p class="mt-1 text-sm text-slate-500">Ubah akun pembeli menjadi akun petani agar bisa menjual hasil panen di Agrilink.</p>
            </div>
            <a href="{{ route('buyer.dashboard') }}" class="ag-btn-secondary">Kembali ke Dashboard</a>
        </div>
    </x-slot:header>

    <div class="grid grid-cols-1 gap-5 xl:grid-cols-[minmax(0,1fr)_360px]">
        <x-ui.card>
            <form method="POST" action="{{ route('buyer.become-farmer.store') }}" class="space-y-5">
                @csrf

                <section class="rounded-3xl border border-emerald-100 bg-emerald-50 p-4">
                    <p class="ag-label">Data Verifikasi</p>
                    <h2 class="mt-2 text-lg font-black text-slate-950">Identitas Petani</h2>
                    <p class="mt-1 text-xs font-semibold leading-5 text-slate-500">Isi NIK atau nomor kelompok tani. Setelah submit, admin akan memverifikasi profil petani.</p>

                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        <label class="block">
                            <span class="mb-2 block text-xs font-bold uppercase text-slate-500">NIK</span>
                            <input type="text" name="nik" value="{{ old('nik') }}" maxlength="16" inputmode="numeric" pattern="[0-9]{16}" data-digits-only placeholder="16 digit angka" class="ag-input">
                        </label>
                        <label class="block">
                            <span class="mb-2 block text-xs font-bold uppercase text-slate-500">ID Kelompok Tani</span>
                            <input type="text" name="farmer_group_id" value="{{ old('farmer_group_id') }}" placeholder="Opsional jika NIK diisi" class="ag-input">
                        </label>
                        <label class="block md:col-span-2">
                            <span class="mb-2 block text-xs font-bold uppercase text-slate-500">Nama Kelompok Tani</span>
                            <input type="text" name="farmer_group_name" value="{{ old('farmer_group_name') }}" class="ag-input">
                        </label>
                    </div>
                </section>

                <section class="rounded-3xl border border-slate-200 bg-white p-4">
                    <p class="ag-label">Data Lahan</p>
                    <h2 class="mt-2 text-lg font-black text-slate-950">Informasi Produksi</h2>
                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        <label class="block">
                            <span class="mb-2 block text-xs font-bold uppercase text-slate-500">Luas Lahan (Ha)</span>
                            <input type="number" step="0.01" min="0.01" name="land_area_hectares" value="{{ old('land_area_hectares') }}" required class="ag-input">
                        </label>
                        <label class="block">
                            <span class="mb-2 block text-xs font-bold uppercase text-slate-500">Komoditas Utama</span>
                            <input type="text" name="main_commodity" value="{{ old('main_commodity') }}" placeholder="Contoh: Cabai Merah" required class="ag-input">
                        </label>
                    </div>
                </section>

                <section class="rounded-3xl border border-slate-200 bg-white p-4">
                    <p class="ag-label">Kontak & Lokasi</p>
                    <h2 class="mt-2 text-lg font-black text-slate-950">Alamat Lahan</h2>
                    <div class="mt-4">
                        <label class="block">
                            <span class="mb-2 block text-xs font-bold uppercase text-slate-500">No. HP</span>
                            <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" inputmode="numeric" pattern="[0-9]{10,15}" maxlength="15" data-digits-only placeholder="08xxxxxxxxxx" aria-invalid="{{ $errors->has('phone') ? 'true' : 'false' }}" class="ag-input @error('phone') border-red-300 focus:border-red-500 focus:ring-red-500/10 @enderror">
                            <x-ui.field-error name="phone" />
                        </label>
                    </div>
                    <div class="mt-5">
                        <x-location-picker
                            id="become-farmer-location-picker"
                            title="Wilayah Lahan"
                            description="Pilih wilayah lahan sampai desa/kelurahan. Tulis gang, RT/RW, dan patokan lahan pada alamat detail."
                            :province-value="old('province', $user->province)"
                            :district-value="old('district', $user->district)"
                            :sub-district-value="old('sub_district', $user->sub_district)"
                            :village-value="old('village', $user->village)"
                            :address-value="old('address', $user->address)"
                            :latitude-value="old('latitude', $user->latitude)"
                            :longitude-value="old('longitude', $user->longitude)"
                            :required="true"
                        />
                    </div>
                </section>

                <div class="rounded-3xl border border-amber-200 bg-amber-50 p-4 text-sm font-semibold leading-6 text-amber-800">
                    Setelah submit, role akun berubah menjadi Petani. Anda tetap bisa membeli produk lewat katalog publik, tetapi dashboard utama akan mengikuti alur petani/penjual.
                </div>

                <button type="submit" class="ag-btn-primary w-full" data-loading-text="Mengirim pendaftaran...">
                    Kirim Pendaftaran Penjual
                </button>
            </form>
        </x-ui.card>

        <aside class="space-y-5">
            <x-ui.card>
                <h2 class="text-xl font-black text-slate-950">Yang Didapat</h2>
                <div class="mt-4 space-y-3 text-sm font-semibold text-slate-600">
                    <div class="rounded-2xl bg-emerald-50 p-3 text-emerald-800">Akses dashboard petani dan produk saya.</div>
                    <div class="rounded-2xl bg-sky-50 p-3 text-sky-800">Bisa menjual hasil panen di marketplace Agrilink.</div>
                    <div class="rounded-2xl bg-amber-50 p-3 text-amber-800">Profil petani masuk antrean verifikasi admin.</div>
                </div>
            </x-ui.card>
        </aside>
    </div>

</x-layouts.app>

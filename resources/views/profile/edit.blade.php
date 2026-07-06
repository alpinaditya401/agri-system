<x-layouts.app :title="'Profil Saya - Agrilink'">
    <x-slot:sidebar>
        @php($sidebarRole = auth()->user()->isAdminMaster() ? 'admin' : auth()->user()->role->name)
        @include($sidebarRole . '._sidebar')
    </x-slot:sidebar>

    <x-slot:header>
        <h1 class="ag-heading flex items-center gap-2">
            <svg class="h-6 w-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0ZM12 14a7 7 0 0 0-7 7h14a7 7 0 0 0-7-7Z" /></svg>
            Profil Saya
        </h1>
        <p class="mt-1 text-sm text-slate-500">Kelola informasi akun, kontak, alamat, dan lokasi Anda.</p>
    </x-slot:header>

    @if (session('status'))
        <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">{{ session('status') }}</div>
    @endif

    @php($profileInitial = strtoupper(substr($user->name ?? 'A', 0, 1)))
    @php($profilePhoto = $user->profile_photo_url)

    <x-ui.card class="mb-5 overflow-hidden" data-reveal>
        <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_320px] lg:items-center">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
                <div class="relative">
                    @if ($profilePhoto)
                        <img id="profilePhotoPreview" src="{{ $profilePhoto }}" alt="Foto profil {{ $user->name }}" class="h-24 w-24 rounded-[2rem] object-cover shadow-xl shadow-emerald-700/20 ring-4 ring-white">
                    @else
                        <div id="profilePhotoFallback" class="flex h-24 w-24 items-center justify-center rounded-[2rem] bg-gradient-to-br from-emerald-500 to-lime-400 text-4xl font-black text-white shadow-xl shadow-emerald-700/20">
                            {{ $profileInitial }}
                        </div>
                        <img id="profilePhotoPreview" src="" alt="Preview foto profil {{ $user->name }}" class="hidden h-24 w-24 rounded-[2rem] object-cover shadow-xl shadow-emerald-700/20 ring-4 ring-white">
                    @endif
                    <span class="absolute -bottom-2 -right-2 flex h-9 w-9 items-center justify-center rounded-2xl border-4 border-white bg-white text-emerald-700 shadow-sm">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8.25h3l1.4-2.1A2.25 2.25 0 019.27 5.1h5.46c.75 0 1.45.38 1.87 1.05L18 8.25h3v10.5A2.25 2.25 0 0118.75 21H5.25A2.25 2.25 0 013 18.75V8.25Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 14.25a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0Z" />
                        </svg>
                    </span>
                </div>
                <div class="min-w-0">
                    <p class="ag-label">Foto Profil</p>
                    <h2 class="mt-2 text-2xl font-black text-slate-950">{{ $user->name }}</h2>
                    <p class="mt-1 text-sm font-semibold text-slate-500">{{ $user->email }}</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <x-ui.badge tone="muted">{{ $user->role->display_name ?? $user->role->name }}</x-ui.badge>
                        <x-ui.badge :tone="$user->is_active ? 'active' : 'inactive'">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</x-ui.badge>
                    </div>
                </div>
            </div>

            <div class="rounded-3xl border border-dashed border-slate-300 bg-slate-50 p-4">
                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="name" value="{{ old('name', $user->name) }}">

                    <label for="profile_photo" class="ag-btn-secondary w-full cursor-pointer">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 16V4m0 0 4 4m-4-4-4 4M4 16.5V18a2 2 0 002 2h12a2 2 0 002-2v-1.5" />
                        </svg>
                        Pilih Foto
                    </label>
                    <input id="profile_photo" name="profile_photo" type="file" accept="image/jpeg,image/png,image/webp" class="sr-only">

                    <button type="submit" class="ag-btn-primary w-full" data-loading-text="Mengunggah foto...">Simpan Foto</button>
                </form>

                @if ($profilePhoto)
                    <form method="POST" action="{{ route('profile.update') }}" class="mt-3">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="name" value="{{ old('name', $user->name) }}">
                        <input type="hidden" name="remove_profile_photo" value="1">
                        <button type="submit" class="ag-btn-secondary w-full text-red-600 hover:bg-red-50 hover:text-red-700" data-loading-text="Menghapus foto...">Hapus Foto</button>
                    </form>
                @endif

                <p class="mt-3 text-xs font-semibold leading-5 text-slate-500">
                    Format JPG, PNG, atau WebP. Maksimal 4 MB. Rasio persegi 512 x 512 px akan terlihat paling rapi.
                </p>
                @error('profile_photo')
                    <p class="mt-2 text-xs font-bold text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </x-ui.card>

    <div class="grid grid-cols-1 gap-5 xl:grid-cols-[minmax(0,1fr)_360px]">
        <x-ui.card>
            <form method="POST" action="{{ route('profile.update') }}" class="space-y-6">
                @csrf
                @method('PATCH')

                <section class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                    <h2 class="text-sm font-black uppercase text-slate-700">Data Pribadi</h2>
                    <label class="mt-4 block">
                        <span class="mb-2 block text-xs font-bold uppercase text-slate-500">Nama Lengkap</span>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="ag-input">
                    </label>
                </section>

                <section class="rounded-3xl border border-slate-200 bg-white p-4">
                    <h2 class="text-sm font-black uppercase text-slate-700">Kontak</h2>
                    <label class="mt-4 block">
                        <span class="mb-2 block text-xs font-bold uppercase text-slate-500">No. Telepon</span>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" inputmode="numeric" pattern="[0-9]{10,15}" maxlength="15" data-digits-only placeholder="08xxxxxxxxxx" aria-invalid="{{ $errors->has('phone') ? 'true' : 'false' }}" class="ag-input @error('phone') border-red-300 focus:border-red-500 focus:ring-red-500/10 @enderror">
                        <x-ui.field-error name="phone" />
                    </label>
                </section>

                <section class="rounded-3xl border border-slate-200 bg-white p-4">
                    <x-location-picker
                        id="profile-location-picker"
                        title="Alamat dan Lokasi"
                        description="Pilih wilayah administrasi, lalu lengkapi gang, RT/RW, nomor rumah, dan patokan di alamat detail."
                        :province-value="old('province', $user->province)"
                        :district-value="old('district', $user->district)"
                        :sub-district-value="old('sub_district', $user->sub_district)"
                        :village-value="old('village', $user->village)"
                        :address-value="old('address', $user->address)"
                        :latitude-value="old('latitude', $user->latitude)"
                        :longitude-value="old('longitude', $user->longitude)"
                    />
                </section>

                @if ($user->isFarmer() && $user->farmerProfile)
                    <section class="rounded-3xl border border-emerald-200 bg-emerald-50 p-4">
                        <h2 class="text-sm font-black uppercase text-emerald-800">Profil Petani</h2>
                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            <label class="block">
                                <span class="mb-2 block text-xs font-bold uppercase text-emerald-800">Luas Lahan (Ha)</span>
                                <input type="number" step="0.01" name="land_area_hectares" value="{{ old('land_area_hectares', $user->farmerProfile->land_area_hectares) }}" class="ag-input">
                            </label>
                            <label class="block">
                                <span class="mb-2 block text-xs font-bold uppercase text-emerald-800">Komoditas Utama</span>
                                <input type="text" name="main_commodity" value="{{ old('main_commodity', $user->farmerProfile->main_commodity) }}" class="ag-input">
                            </label>
                            <label class="block md:col-span-2">
                                <span class="mb-2 block text-xs font-bold uppercase text-emerald-800">Nama Kelompok Tani</span>
                                <input type="text" name="farmer_group_name" value="{{ old('farmer_group_name', $user->farmerProfile->farmer_group_name) }}" class="ag-input">
                            </label>
                        </div>
                    </section>
                @endif

                @if ($user->isDistributor() && $user->distributorProfile)
                    <section class="rounded-3xl border border-sky-200 bg-sky-50 p-4">
                        <h2 class="text-sm font-black uppercase text-sky-800">Profil Distributor</h2>
                        <label class="mt-4 block">
                            <span class="mb-2 block text-xs font-bold uppercase text-sky-800">Nama Perusahaan</span>
                            <input type="text" name="company_name" value="{{ old('company_name', $user->distributorProfile->company_name) }}" class="ag-input">
                        </label>
                    </section>
                @endif

                <button type="submit" class="ag-btn-primary" data-loading-text="Menyimpan...">Simpan Perubahan</button>
            </form>
        </x-ui.card>

        <aside class="space-y-5">
            <x-ui.card>
                <h2 class="text-xl font-black text-slate-950">Ringkasan Akun</h2>
                <div class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between gap-4"><span class="font-semibold text-slate-500">Role</span><span class="font-black text-slate-900">{{ $user->role->display_name ?? $user->role->name }}</span></div>
                    <div class="flex justify-between gap-4"><span class="font-semibold text-slate-500">Bergabung</span><span class="font-black text-slate-900">{{ $user->created_at->translatedFormat('d M Y') }}</span></div>
                    <div class="flex justify-between gap-4"><span class="font-semibold text-slate-500">Status</span><x-ui.badge :tone="$user->is_active ? 'active' : 'inactive'">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</x-ui.badge></div>
                    @if ($user->isFarmer() && $user->farmerProfile)
                        <div class="flex justify-between gap-4"><span class="font-semibold text-slate-500">Verifikasi</span><x-ui.badge :tone="$user->farmerProfile->verification_status ?? 'muted'">{{ ucfirst($user->farmerProfile->verification_status ?? '-') }}</x-ui.badge></div>
                    @endif
                </div>
            </x-ui.card>
        </aside>
    </div>

    @push('scripts')
        <script>
            document.getElementById('profile_photo')?.addEventListener('change', (event) => {
                const file = event.target.files?.[0];
                if (!file) return;

                const preview = document.getElementById('profilePhotoPreview');
                const fallback = document.getElementById('profilePhotoFallback');

                if (preview) {
                    preview.src = URL.createObjectURL(file);
                    preview.classList.remove('hidden');
                }

                fallback?.classList.add('hidden');
            });
        </script>
    @endpush
</x-layouts.app>

<x-layouts.app :title="$user->name . ' – Agrilink'">
    <x-slot:sidebar>
        @include('admin._sidebar')
    </x-slot:sidebar>

    <x-slot:header>
        <h1 class="text-lg font-bold text-gray-800">{{ $user->name }}</h1>
        <p class="text-gray-500 text-sm">Detail akun pengguna.</p>
    </x-slot:header>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        @php
            $actor = auth()->user();
            $canManageUser = $actor->isAdminMaster()
                ? (!$user->isAdminMaster() && $actor->id !== $user->id)
                : (!$user->isAdmin() && !$user->isAdminMaster());
        @endphp
        <div class="md:col-span-2 bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
            <div class="flex items-center gap-4 mb-5">
                <div class="w-14 h-14 bg-emerald-100 rounded-full flex items-center justify-center text-emerald-700 font-bold text-xl">{{ substr($user->name, 0, 1) }}</div>
                <div>
                    <p class="font-bold text-gray-800">{{ $user->name }}</p>
                    <p class="text-sm text-gray-500">{{ $user->email }}</p>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div><p class="text-xs text-gray-400">Role</p><p class="font-semibold text-gray-800">{{ $user->role->display_name ?? $user->role->name ?? '-' }}</p></div>
                <div><p class="text-xs text-gray-400">Status</p><p class="font-semibold text-gray-800">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</p></div>
                <div><p class="text-xs text-gray-400">No. Telepon</p><p class="font-semibold text-gray-800">{{ $user->phone ?? '-' }}</p></div>
                <div><p class="text-xs text-gray-400">Bergabung</p><p class="font-semibold text-gray-800">{{ $user->created_at->translatedFormat('d F Y') }}</p></div>
                @if ($user->district)
                    <div><p class="text-xs text-gray-400">Wilayah</p><p class="font-semibold text-gray-800">{{ $user->district }}, {{ $user->province }}</p></div>
                @endif
            </div>

            @if ($user->farmerProfile)
                <div class="mt-5 pt-5 border-t border-gray-100">
                    <h2 class="font-bold text-gray-800 mb-3">Profil Petani</h2>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div><p class="text-xs text-gray-400">NIK</p><p class="font-semibold text-gray-800">{{ $user->farmerProfile->nik ?? '-' }}</p></div>
                        <div><p class="text-xs text-gray-400">Kelompok Tani</p><p class="font-semibold text-gray-800">{{ $user->farmerProfile->farmer_group_name ?? '-' }}</p></div>
                        <div><p class="text-xs text-gray-400">Status Verifikasi</p><p class="font-semibold text-gray-800">{{ ucfirst($user->farmerProfile->verification_status ?? '-') }}</p></div>
                    </div>
                </div>
            @endif
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm h-fit">
            <h2 class="font-bold text-gray-800 mb-3">Aksi</h2>
            <div class="space-y-2">
                @if($canManageUser)
                    <a href="{{ route('admin.users.edit', $user) }}" class="block text-center bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 rounded-xl text-sm transition-colors">Edit Pengguna</a>
                    <form method="POST" action="{{ route('admin.users.toggle-active', $user) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="w-full bg-amber-50 hover:bg-amber-100 text-amber-700 font-semibold py-2.5 rounded-xl text-sm transition-colors">
                            {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Hapus pengguna ini secara permanen?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full bg-red-50 hover:bg-red-100 text-red-600 font-semibold py-2.5 rounded-xl text-sm transition-colors">Hapus Pengguna</button>
                    </form>
                @else
                    <div class="rounded-xl bg-gray-50 p-3 text-sm text-gray-500">
                        Akun ini terkunci dari kontrol role Anda.
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>

<x-layouts.app :title="'Manajemen Pengguna – Agrilink'">
    <x-slot:sidebar>
        @include('admin._sidebar')
    </x-slot:sidebar>

    <x-slot:header>
        <h1 class="ag-heading flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Manajemen Pengguna
        </h1>
        <p class="mt-1 text-sm text-slate-500">
            {{ auth()->user()->isAdminMaster()
                ? 'Admin Master dapat mengatur role semua user dan mengontrol admin biasa.'
                : 'Admin biasa dapat mengelola user non-admin. Admin Master tetap terkunci.' }}
        </p>
    </x-slot:header>

    <div class="flex justify-end mb-5">
        <a href="{{ route('admin.users.create') }}" class="ag-btn-primary">Tambah Pengguna</a>
    </div>

    <div class="ag-table-wrap">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                        <th class="p-4 font-semibold">Nama</th>
                        <th class="p-4 font-semibold">Email</th>
                        <th class="p-4 font-semibold">Role</th>
                        <th class="p-4 font-semibold">Status</th>
                        <th class="p-4 font-semibold">Bergabung</th>
                        <th class="p-4 font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-gray-100">
                    @forelse ($users as $user)
                        @php
                            $actor = auth()->user();
                            $canManageUser = $actor->isAdminMaster()
                                ? (!$user->isAdminMaster() && $actor->id !== $user->id)
                                : (!$user->isAdmin() && !$user->isAdminMaster());
                        @endphp
                        <tr class="hover:bg-gray-50/50">
                            <td class="p-4 flex items-center gap-3">
                                <div class="w-9 h-9 bg-emerald-100 rounded-full flex items-center justify-center text-emerald-700 font-bold text-xs">{{ substr($user->name, 0, 1) }}</div>
                                <span class="font-medium text-gray-800">{{ $user->name }}</span>
                            </td>
                            <td class="p-4 text-gray-600">{{ $user->email }}</td>
                            <td class="p-4">
                                <x-ui.badge tone="muted">{{ $user->role->display_name ?? $user->role->name ?? '-' }}</x-ui.badge>
                                @if($user->isAdminMaster())
                                    <span class="ml-1 bg-slate-900 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">Master</span>
                                @endif
                            </td>
                            <td class="p-4">
                                <x-ui.badge :tone="$user->is_active ? 'active' : 'inactive'">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</x-ui.badge>
                            </td>
                            <td class="p-4 text-gray-400 text-xs">{{ $user->created_at->translatedFormat('d M Y') }}</td>
                            <td class="p-4">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.users.show', $user) }}" class="text-gray-500 hover:text-gray-700 font-medium text-xs">Lihat</a>
                                    @if($canManageUser)
                                        <a href="{{ route('admin.users.edit', $user) }}" class="text-emerald-600 hover:text-emerald-800 font-medium text-xs">Edit</a>
                                        <form method="POST" action="{{ route('admin.users.toggle-active', $user) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="text-amber-600 hover:text-amber-800 font-medium text-xs">{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" data-confirm="Hapus pengguna ini?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700 font-medium text-xs">Hapus</button>
                                        </form>
                                    @else
                                        <span class="text-[11px] font-semibold text-gray-400">Terkunci</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-gray-400 text-sm italic">Belum ada pengguna.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-5">
        {{ $users->links() }}
    </div>
</x-layouts.app>

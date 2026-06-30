<x-layouts.app :title="'Kelola Artikel – Agrilink'">
    <x-slot:sidebar>
        @include('admin._sidebar')
    </x-slot:sidebar>

    <x-slot:header>
        <h1 class="ag-heading flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            Kelola Artikel
        </h1>
        <p class="mt-1 text-sm text-slate-500">Tulis dan kelola artikel edukasi untuk pengguna Agrilink.</p>
    </x-slot:header>

    <div class="flex justify-end mb-5">
        <a href="{{ route('admin.artikel.create') }}" class="ag-btn-primary">Tambah Artikel</a>
    </div>

    <div class="ag-table-wrap">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                        <th class="p-4 font-semibold">Judul</th>
                        <th class="p-4 font-semibold">Kategori</th>
                        <th class="p-4 font-semibold">Penulis</th>
                        <th class="p-4 font-semibold">Status</th>
                        <th class="p-4 font-semibold">Dilihat</th>
                        <th class="p-4 font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-gray-100">
                    @forelse ($articles as $article)
                        <tr class="hover:bg-gray-50/50">
                            <td class="p-4 font-medium text-gray-800 max-w-xs truncate">{{ $article->title }}</td>
                            <td class="p-4 text-gray-500">{{ $article->category ?? '-' }}</td>
                            <td class="p-4 text-gray-600">{{ $article->author->name ?? 'Admin' }}</td>
                            <td class="p-4">
                                @php
                                    $badge = match($article->status) {
                                        'published' => 'bg-emerald-50 text-emerald-700',
                                        'draft' => 'bg-yellow-50 text-yellow-700',
                                        default => 'bg-gray-100 text-gray-500',
                                    };
                                @endphp
                                <x-ui.badge :tone="$article->status">{{ ucfirst($article->status) }}</x-ui.badge>
                            </td>
                            <td class="p-4 text-gray-500">{{ $article->view_count }}</td>
                            <td class="p-4">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.artikel.show', $article) }}" class="text-gray-500 hover:text-gray-700 font-medium text-xs">Lihat</a>
                                    <a href="{{ route('admin.artikel.edit', $article) }}" class="text-emerald-600 hover:text-emerald-800 font-medium text-xs">Edit</a>
                                    <form method="POST" action="{{ route('admin.artikel.destroy', $article) }}" data-confirm="Hapus artikel ini?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 font-medium text-xs">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-gray-400 text-sm italic">Belum ada artikel.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $articles->links() }}</div>
    </div>
</x-layouts.app>

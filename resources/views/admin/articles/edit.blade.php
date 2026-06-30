<x-layouts.app :title="'Edit Artikel – Agrilink'">
    <x-slot:sidebar>
        @include('admin._sidebar')
    </x-slot:sidebar>

    <x-slot:header>
        <h1 class="text-lg font-bold text-gray-800">Edit Artikel</h1>
        <p class="text-gray-500 text-sm">Perbarui artikel "{{ $artikel->title }}".</p>
    </x-slot:header>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 max-w-3xl">
        <form method="POST" action="{{ route('admin.artikel.update', $artikel) }}" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="text-xs font-semibold text-gray-500">Judul</label>
                <input type="text" name="title" value="{{ old('title', $artikel->title) }}" required
                       class="w-full mt-1 px-3 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:border-emerald-400">
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500">Ringkasan (Excerpt)</label>
                <textarea name="excerpt" rows="2" maxlength="500" class="w-full mt-1 px-3 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:border-emerald-400">{{ old('excerpt', $artikel->excerpt) }}</textarea>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500">Konten</label>
                <textarea name="content" rows="10" required class="w-full mt-1 px-3 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:border-emerald-400">{{ old('content', $artikel->content) }}</textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-semibold text-gray-500">Kategori</label>
                    <input type="text" name="category" value="{{ old('category', $artikel->category) }}"
                           class="w-full mt-1 px-3 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:border-emerald-400">
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500">Status</label>
                    <select name="status" required class="w-full mt-1 px-3 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:border-emerald-400">
                        <option value="draft" {{ old('status', $artikel->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ old('status', $artikel->status) === 'published' ? 'selected' : '' }}>Publikasikan</option>
                        <option value="archived" {{ old('status', $artikel->status) === 'archived' ? 'selected' : '' }}>Arsipkan</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500">URL Gambar Cover (opsional)</label>
                <input type="text" name="cover_image" value="{{ old('cover_image', $artikel->cover_image) }}"
                       class="w-full mt-1 px-3 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:border-emerald-400">
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-5 py-2.5 rounded-xl text-sm transition-colors">Simpan Perubahan</button>
                <a href="{{ route('admin.artikel.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 font-semibold px-5 py-2.5 rounded-xl text-sm transition-colors">Batal</a>
            </div>
        </form>
    </div>
</x-layouts.app>

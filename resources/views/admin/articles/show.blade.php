<x-layouts.app :title="$artikel->title . ' – Agrilink'">
    <x-slot:sidebar>
        @include('admin._sidebar')
    </x-slot:sidebar>

    <x-slot:header>
        <h1 class="text-lg font-bold text-gray-800">{{ $artikel->title }}</h1>
        <p class="text-gray-500 text-sm">{{ ucfirst($artikel->status) }} · {{ $artikel->view_count }} kali dilihat</p>
    </x-slot:header>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 max-w-3xl">
        @if ($artikel->cover_image)
            <img src="{{ asset($artikel->cover_image) }}" class="w-full rounded-xl mb-5">
        @endif
        @if ($artikel->category)
            <span class="bg-emerald-50 text-emerald-700 text-[10px] font-bold px-2 py-1 rounded-full uppercase">{{ $artikel->category }}</span>
        @endif
        <p class="text-sm text-gray-500 mt-3">{{ $artikel->excerpt }}</p>
        <div class="prose prose-sm max-w-none mt-5 text-gray-700 leading-relaxed">
            {!! nl2br(e($artikel->content)) !!}
        </div>

        @if (collect($artikel->tags ?? [])->contains('bps'))
            <div class="mt-6 rounded-2xl border border-emerald-100 bg-emerald-50/50 p-4">
                <h2 class="font-bold text-gray-800 text-sm">Link Tabel BPS Utuh</h2>
                <div class="mt-3 space-y-2">
                    @foreach (config('bps_sources.tables', []) as $table)
                        <a href="{{ $table['url'] }}" target="_blank" rel="noopener" class="block rounded-xl bg-white p-3 text-xs text-emerald-700 hover:bg-emerald-50">
                            {{ $table['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="flex gap-3 mt-6 pt-5 border-t border-gray-100">
            <a href="{{ route('admin.artikel.edit', $artikel) }}" class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-5 py-2.5 rounded-xl text-sm transition-colors">Edit Artikel</a>
            <a href="{{ route('admin.artikel.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 font-semibold px-5 py-2.5 rounded-xl text-sm transition-colors">Kembali</a>
        </div>
    </div>
</x-layouts.app>

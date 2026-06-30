@props([
    'filters' => [],
    'options' => [],
    'title' => 'Filter Wilayah',
    'description' => 'Pilih provinsi atau kabupaten/kota untuk menyesuaikan data dashboard.',
])

@php
    $province = $filters['province'] ?? request('province');
    $district = $filters['district'] ?? request('district');
    $provinces = collect($options['provinces'] ?? []);
    $districts = collect($options['districts'] ?? []);
    $activeLabel = \App\Support\DashboardRegion::label(['province' => $province, 'district' => $district]);
@endphp

<section {{ $attributes->merge(['class' => 'ag-card mb-5 p-4 md:p-5']) }}>
    <form method="GET" action="{{ url()->current() }}" class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_220px_220px_auto] lg:items-end">
        <div>
            <p class="ag-label">{{ $title }}</p>
            <h2 class="mt-2 text-lg font-black text-slate-950">{{ $activeLabel }}</h2>
            <p class="mt-1 text-xs font-semibold leading-5 text-slate-500">{{ $description }}</p>
        </div>

        <label class="block">
            <span class="mb-2 block text-xs font-bold uppercase text-slate-500">Provinsi</span>
            <select name="province" class="ag-select">
                <option value="">Semua Provinsi</option>
                @foreach ($provinces as $item)
                    <option value="{{ $item }}" @selected($province === $item)>{{ $item }}</option>
                @endforeach
            </select>
        </label>

        <label class="block">
            <span class="mb-2 block text-xs font-bold uppercase text-slate-500">Kabupaten/Kota</span>
            <select name="district" class="ag-select">
                <option value="">Semua Kabupaten/Kota</option>
                @foreach ($districts as $item)
                    <option value="{{ $item }}" @selected($district === $item)>{{ $item }}</option>
                @endforeach
            </select>
        </label>

        <div class="flex flex-wrap gap-2">
            <button type="submit" class="ag-btn-primary px-5 py-3">Terapkan</button>
            @if ($province || $district)
                <a href="{{ url()->current() }}" class="ag-btn-secondary px-5 py-3">Reset</a>
            @endif
        </div>
    </form>
</section>

@props(['padded' => true])

<div {{ $attributes->merge(['class' => 'ag-card ' . ($padded ? 'p-5 md:p-6' : '')]) }}>
    {{ $slot }}
</div>

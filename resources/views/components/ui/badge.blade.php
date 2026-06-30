@props([
    'tone' => 'muted',
])

@php
    $classes = [
        'pending' => 'ag-status ag-status-pending',
        'approved' => 'ag-status ag-status-success',
        'verified' => 'ag-status ag-status-success',
        'active' => 'ag-status ag-status-success',
        'paid' => 'ag-status ag-status-success',
        'processing' => 'ag-status ag-status-info',
        'confirmed' => 'ag-status ag-status-info',
        'shipped' => 'ag-status ag-status-info',
        'delivered' => 'ag-status ag-status-info',
        'completed' => 'ag-status ag-status-success',
        'rejected' => 'ag-status ag-status-danger',
        'cancelled' => 'ag-status ag-status-danger',
        'inactive' => 'ag-status ag-status-muted',
        'draft' => 'ag-status ag-status-muted',
        'published' => 'ag-status ag-status-success',
        'archived' => 'ag-status ag-status-muted',
        'success' => 'ag-status ag-status-success',
        'warning' => 'ag-status ag-status-pending',
        'danger' => 'ag-status ag-status-danger',
        'info' => 'ag-status ag-status-info',
        'muted' => 'ag-status ag-status-muted',
    ];
@endphp

<span {{ $attributes->merge(['class' => $classes[$tone] ?? $classes['muted']]) }}>
    {{ $slot }}
</span>

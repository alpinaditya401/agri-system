<a href="{{ route(auth()->user()->isAdminMaster() ? 'admin-master.dashboard' : 'admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') || request()->routeIs('admin-master.dashboard') ? 'active' : '' }}">
    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
    <span class="whitespace-nowrap">Dashboard</span>
</a>
<a href="{{ route('admin.fertilizer.quota.index') }}" class="sidebar-link {{ request()->routeIs('admin.fertilizer.*') ? 'active' : '' }}">
    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
    <span class="whitespace-nowrap">Kuota Pupuk</span>
</a>
<a href="{{ route('admin.reports.transactions') }}" class="sidebar-link {{ request()->routeIs('admin.reports.transactions') ? 'active' : '' }}">
    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
    <span class="whitespace-nowrap">Transaksi</span>
</a>
<a href="{{ route('admin.reports.fertilizer') }}" class="sidebar-link {{ request()->routeIs('admin.reports.fertilizer') ? 'active' : '' }}">
    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 014-4h4m0 0l-3-3m3 3l-3 3M4 7h16M4 7a2 2 0 002 2h12a2 2 0 002-2M4 7a2 2 0 012-2h12a2 2 0 012 2"/></svg>
    <span class="whitespace-nowrap">Distribusi Pupuk</span>
</a>
<a href="{{ route('admin.users.index') }}" class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
    <span class="whitespace-nowrap">Manajemen Pengguna</span>
</a>
@if(auth()->user()->isAdminMaster())
    <a href="{{ route('admin-master.payment-settings.edit') }}" class="sidebar-link {{ request()->routeIs('admin-master.payment-settings.*') ? 'active' : '' }}">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15.75A3.75 3.75 0 1012 8.25a3.75 3.75 0 000 7.5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 12a7.5 7.5 0 01-.08 1.08l2.02 1.58-1.92 3.32-2.38-.96a7.53 7.53 0 01-1.86 1.08L14.92 21h-3.84l-.36-2.9a7.53 7.53 0 01-1.86-1.08l-2.38.96-1.92-3.32 2.02-1.58a7.7 7.7 0 010-2.16L4.56 9.34l1.92-3.32 2.38.96a7.53 7.53 0 011.86-1.08L11.08 3h3.84l.36 2.9a7.53 7.53 0 011.86 1.08l2.38-.96 1.92 3.32-2.02 1.58c.05.35.08.71.08 1.08z"/></svg>
        <span class="whitespace-nowrap">Gerbang Pembayaran</span>
    </a>
@endif
<a href="{{ route('admin.farmers.verify.index') }}" class="sidebar-link {{ request()->routeIs('admin.farmers.*') ? 'active' : '' }}">
    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <span class="whitespace-nowrap">Verifikasi Petani</span>
</a>
<a href="{{ route('admin.reports.prices') }}" class="sidebar-link {{ request()->routeIs('admin.reports.prices') ? 'active' : '' }}">
    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
    <span class="whitespace-nowrap">Harga Komoditas</span>
    <span class="ml-auto text-[10px] bg-emerald-600 px-1.5 py-0.5 rounded-full">Live</span>
</a>
<a href="{{ route('admin.artikel.index') }}" class="sidebar-link {{ request()->routeIs('admin.artikel.*') ? 'active' : '' }}">
    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
    <span class="whitespace-nowrap">Kelola Artikel</span>
</a>
<a href="{{ route('products.index') }}" class="sidebar-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
    <span class="whitespace-nowrap">Marketplace</span>
</a>

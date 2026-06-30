<a href="{{ route('buyer.dashboard') }}" class="sidebar-link {{ request()->routeIs('buyer.dashboard') ? 'active' : '' }}">
    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
    <span class="whitespace-nowrap">Dashboard</span>
</a>
<a href="{{ route('products.index') }}" class="sidebar-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
    <span class="whitespace-nowrap">Belanja Produk</span>
</a>
<a href="{{ route('buyer.cart.index') }}" class="sidebar-link {{ request()->routeIs('buyer.cart.*') ? 'active' : '' }}">
    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
    <span class="whitespace-nowrap">Keranjang</span>
</a>
<a href="{{ route('buyer.orders.index') }}" class="sidebar-link {{ request()->routeIs('buyer.orders.*') ? 'active' : '' }}">
    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
    <span class="whitespace-nowrap">Pesanan Saya</span>
</a>
<a href="{{ route('buyer.become-farmer.create') }}" class="sidebar-link {{ request()->routeIs('buyer.become-farmer.*') ? 'active' : '' }}">
    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.75c-3.75 0-6.75 2.1-6.75 4.7 0 4.05 4.65 5.8 6.75 8.05 2.1-2.25 6.75-4 6.75-8.05 0-2.6-3-4.7-6.75-4.7Zm0 0V3m-4.5 7.5c1.5 0 3 .75 4.5 2.25 1.5-1.5 3-2.25 4.5-2.25" /></svg>
    <span class="whitespace-nowrap">Daftar Jadi Penjual</span>
</a>

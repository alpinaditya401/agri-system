<x-layouts.app :title="'Notifikasi – Agrilink'">
    <x-slot:sidebar>
        @php($sidebarRole = auth()->user()->isAdminMaster() ? 'admin' : auth()->user()->role->name)
        @include($sidebarRole . '._sidebar')
    </x-slot:sidebar>

    <x-slot:header>
        <h1 class="ag-heading flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            Notifikasi
            <span id="unreadBadge" class="bg-red-500 text-white text-xs font-black px-2.5 py-1 rounded-full hidden">0</span>
        </h1>
        <p class="mt-1 text-sm text-slate-500">Pantau update pengiriman, harga, stok, dan pesan masuk.</p>
    </x-slot:header>

    <div class="mb-5 flex flex-col justify-between gap-3 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm md:flex-row md:items-center">
        <div class="flex gap-2 flex-wrap">
            <button onclick="filterNotif('all')" id="f-all" class="nf-btn active">Semua</button>
            <button onclick="filterNotif('arrived')" id="f-arrived" class="nf-btn">Pengiriman</button>
            <button onclick="filterNotif('price')" id="f-price" class="nf-btn">Harga</button>
            <button onclick="filterNotif('low_stock')" id="f-low_stock" class="nf-btn">Stok</button>
            <button onclick="filterNotif('chat')" id="f-chat" class="nf-btn">Chat</button>
        </div>
        <button onclick="markAll()" class="ag-btn-secondary self-start px-4 py-2 text-xs md:self-auto">
            Tandai Semua Dibaca
        </button>
    </div>

    <div id="stokBanner" class="mb-6 hidden rounded-3xl border border-orange-200 bg-gradient-to-r from-orange-50 to-red-50 p-5"></div>

    <div id="notifSkeleton" class="space-y-3">
        @for ($i = 0; $i < 4; $i++)
        <div class="flex gap-4 rounded-3xl border border-slate-200 bg-white p-5 animate-pulse">
            <div class="w-11 h-11 rounded-xl bg-gray-100 flex-shrink-0"></div>
            <div class="flex-1">
                <div class="h-3.5 bg-gray-100 rounded w-2/3 mb-2"></div>
                <div class="h-2.5 bg-gray-100 rounded w-full mb-1"></div>
                <div class="h-2.5 bg-gray-100 rounded w-1/2"></div>
            </div>
        </div>
        @endfor
    </div>

    <div id="notifList" class="space-y-3 hidden"></div>
    <div id="emptyState" class="hidden rounded-3xl border border-dashed border-slate-300 bg-white py-16 text-center">
        <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100">
            <svg class="w-7 h-7 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.143 17.082a24.248 24.248 0 003.844.148m-3.844-.148a23.856 23.856 0 01-5.455-1.31 8.964 8.964 0 002.3-5.542m3.155 6.852a3 3 0 005.667 1.97m1.965-2.277L21 21m-4.225-4.225a23.81 23.81 0 003.536-1.003A8.967 8.967 0 0118 9.75V9A6 6 0 006.53 6.53m10.245 10.245L6.53 6.53M3 3l3.53 3.53"/></svg>
        </div>
        <p class="font-bold text-slate-500">Belum ada notifikasi</p>
    </div>

    <style>
        .nf-btn { padding:8px 16px; border-radius:999px; font-size:.72rem; font-weight:800; border:1.5px solid #e2e8f0; color:#64748b; background:#fff; cursor:pointer; transition:.2s; }
        .nf-btn:hover, .nf-btn.active { background:#047857; color:#fff; border-color:#047857; }
    </style>

    @push('scripts')
    <script>
        const NOTIF_FETCH = '{{ route("api.notifications.fetch") }}';
        const NOTIF_READ = '{{ route("api.notifications.read") }}';
        const NOTIF_READ_ALL = '{{ route("api.notifications.read-all") }}';
        const NOTIF_STOCK_ALERTS = '{{ route("api.notifications.stock-alerts") }}';
        const CSRF_TOKEN = '{{ csrf_token() }}';
        let allNotif = [];
        let currentFilter = 'all';

        const typeMap = {
            arrived:   { color: '#10b981', bg: 'rgba(16,185,129,.12)', icon: 'M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0zM13 17H8m5-13H3a1 1 0 00-1 1v9a1 1 0 001 1h2m10-11l4 4m0 0v6h-4m0-6h-3v6h3' },
            price:     { color: '#f59e0b', bg: 'rgba(245,158,11,.12)', icon: 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z' },
            low_stock: { color: '#ef4444', bg: 'rgba(239,68,68,.12)', icon: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z' },
            chat:      { color: '#3b82f6', bg: 'rgba(59,130,246,.12)', icon: 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z' },
            info:      { color: '#8b5cf6', bg: 'rgba(139,92,246,.12)', icon: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
            alert:     { color: '#f87171', bg: 'rgba(248,113,113,.12)', icon: 'M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z' },
        };

        async function loadNotif() {
            try {
                const res = await fetch(NOTIF_FETCH);
                const json = await res.json();
                if (json.status !== 'success') throw new Error(json.msg);

                allNotif = json.data.list || [];
                const unread = json.data.unread || 0;

                document.getElementById('notifSkeleton').classList.add('hidden');
                document.getElementById('notifList').classList.remove('hidden');

                const badge = document.getElementById('unreadBadge');
                badge.textContent = unread;
                badge.classList.toggle('hidden', unread === 0);

                renderNotif(allNotif);
                loadStokAlerts();
            } catch (e) {
                document.getElementById('notifSkeleton').classList.add('hidden');
                document.getElementById('notifList').classList.remove('hidden');
                document.getElementById('emptyState').classList.remove('hidden');
            }
        }

        function renderNotif(list) {
            const container = document.getElementById('notifList');
            const filtered = currentFilter === 'all' ? list : list.filter(n => n.tipe === currentFilter);
            document.getElementById('emptyState').classList.toggle('hidden', filtered.length > 0);
            container.innerHTML = filtered.map(n => {
                const t = typeMap[n.tipe] || typeMap.info;
                const time = n.created_at ? new Date(n.created_at).toLocaleString('id', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' }) : '-';
                return `
                <div class="notif-card bg-white rounded-2xl border border-gray-100 p-5 flex gap-4 items-start transition-all hover:shadow-md cursor-pointer ${!Number(n.dibaca) ? 'border-l-4 border-l-emerald-500' : ''}"
                     onclick="markRead(${n.id}, this)">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0" style="background:${t.bg}">
                        <svg class="w-5 h-5" style="color:${t.color}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${t.icon}"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-start gap-2">
                            <p class="font-bold text-gray-800 text-sm">${escHtml(n.judul)}</p>
                            ${!Number(n.dibaca) ? '<div class="w-2.5 h-2.5 bg-emerald-500 rounded-full flex-shrink-0 mt-1"></div>' : ''}
                        </div>
                        <p class="text-xs text-gray-500 mt-1 leading-relaxed">${escHtml(n.pesan)}</p>
                        <p class="text-[11px] text-gray-300 mt-2">${time}</p>
                    </div>
                </div>`;
            }).join('');
        }

        async function loadStokAlerts() {
            try {
                const res = await fetch(NOTIF_STOCK_ALERTS);
                const json = await res.json();
                if (json.status !== 'success' || !json.data?.length) return;
                const banner = document.getElementById('stokBanner');
                const items = json.data.map(a => `
                    <div class="bg-white border border-orange-200 rounded-xl px-3 py-2 text-xs">
                        <span class="font-bold text-red-600">${escHtml(a.nama)}</span> - Sisa <strong>${a.stok_saat_ini} kg</strong>
                        <span class="text-gray-400">(min. ${a.stok_min} kg)</span>
                        <div class="mt-1 bg-gray-100 rounded h-1.5">
                            <div class="h-1.5 rounded bg-red-400" style="width:${Math.min(100, Math.round(a.stok_saat_ini / a.stok_min * 100))}%"></div>
                        </div>
                    </div>`).join('');
                banner.innerHTML = `
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 bg-orange-100 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                        </div>
                        <div class="flex-1">
                <p class="font-bold text-orange-800 text-sm mb-2">Alert Stok Menipis - ${json.data.length} item</p>
                            <div class="flex flex-wrap gap-3">${items}</div>
                        </div>
                    </div>`;
                banner.classList.remove('hidden');
            } catch (e) {}
        }

        async function markRead(id, el) {
            el.classList.remove('border-l-4', 'border-l-emerald-500');
            el.querySelector('.bg-emerald-500.rounded-full')?.remove();
            await fetch(NOTIF_READ, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                body: JSON.stringify({ id }),
            });
        }

        async function markAll() {
            await fetch(NOTIF_READ_ALL, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
            });
            document.querySelectorAll('.border-l-4.border-l-emerald-500').forEach(el => {
                el.classList.remove('border-l-4', 'border-l-emerald-500');
                el.querySelector('.bg-emerald-500.rounded-full')?.remove();
            });
            document.getElementById('unreadBadge').classList.add('hidden');
        }

        function filterNotif(type) {
            currentFilter = type;
            document.querySelectorAll('.nf-btn').forEach(b => b.classList.remove('active'));
            document.getElementById('f-' + type).classList.add('active');
            renderNotif(allNotif);
        }

        function escHtml(s) {
            if (!s) return '';
            return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        }

        loadNotif();
        setInterval(loadNotif, 30000);
    </script>
    @endpush
</x-layouts.app>

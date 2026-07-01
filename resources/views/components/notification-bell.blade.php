<div class="js-notif-bell relative">
    <button type="button" class="js-notif-bell-btn relative flex h-11 w-11 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700" aria-label="Buka notifikasi">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 0 0-4-5.7V5a2 2 0 1 0-4 0v.3A6 6 0 0 0 6 11v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0v1a3 3 0 1 1-6 0v-1m6 0H9" />
        </svg>
        <span class="js-notif-count hidden absolute -right-1 -top-1 min-w-5 rounded-full bg-red-500 px-1.5 py-0.5 text-center text-[10px] font-black text-white ring-2 ring-white">0</span>
    </button>

    <div class="js-notif-bell-dropdown hidden absolute right-0 z-50 mt-3 w-80 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl shadow-slate-900/10">
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
            <p class="text-sm font-bold text-slate-900">Notifikasi</p>
            <a href="{{ route('notifications.index') }}" class="text-xs font-bold text-emerald-700 hover:text-emerald-800">Lihat semua</a>
        </div>
        <div class="js-notif-bell-list max-h-80 overflow-y-auto divide-y divide-slate-100">
            <p class="py-6 text-center text-xs font-semibold text-slate-400">Memuat...</p>
        </div>
    </div>
</div>

<script>
(function () {
    document.querySelectorAll('.js-notif-bell').forEach((wrap) => {
        if (wrap.dataset.bound === '1') return;
        wrap.dataset.bound = '1';

        const btn = wrap.querySelector('.js-notif-bell-btn');
        const dropdown = wrap.querySelector('.js-notif-bell-dropdown');
        const list = wrap.querySelector('.js-notif-bell-list');

        btn?.addEventListener('click', async (event) => {
            event.stopPropagation();
            const willOpen = dropdown?.classList.contains('hidden');
            dropdown?.classList.toggle('hidden');
            if (willOpen) await loadBellNotif(list);
        });

        document.addEventListener('click', (event) => {
            if (!wrap.contains(event.target)) {
                dropdown?.classList.add('hidden');
            }
        });
    });

    async function loadBellNotif(list) {
        if (!list) return;
        try {
            const res = await fetch('{{ route("api.notifications.summary") }}', {
                headers: { 'Accept': 'application/json' },
            });
            const json = await res.json();
            if (json.status !== 'success' || !json.recent?.length) {
                list.innerHTML = '<p class="py-6 text-center text-xs font-semibold text-slate-400">Belum ada notifikasi</p>';
                return;
            }

            list.innerHTML = json.recent.map((notif) => `
                <div class="px-4 py-3 transition hover:bg-slate-50 ${!notif.dibaca ? 'bg-emerald-50/60' : ''}">
                    <p class="text-xs font-bold text-slate-900">${escBell(notif.judul)}</p>
                    <p class="mt-1 text-[11px] leading-5 text-slate-500">${escBell(notif.pesan)}</p>
                </div>
            `).join('');
        } catch (e) {
            list.innerHTML = '<p class="py-6 text-center text-xs font-semibold text-slate-400">Gagal memuat notifikasi</p>';
        }
    }

    function escBell(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }
})();
</script>

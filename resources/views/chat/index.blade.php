<x-layouts.app :title="'Pesan / Chat – Agrilink'">
    <x-slot:sidebar>
        @php($sidebarRole = auth()->user()->isAdminMaster() ? 'admin' : auth()->user()->role->name)
        @include($sidebarRole . '._sidebar')
    </x-slot:sidebar>

    <x-slot:header>
        <h1 class="ag-heading flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            Pesan / Chat
        </h1>
        <p class="mt-1 text-sm text-slate-500">Komunikasi langsung antar petani, distributor, pembeli, dan admin.</p>
    </x-slot:header>

    <div class="mb-4 flex gap-2 md:hidden">
        <button type="button" onclick="openContacts()" class="ag-btn-secondary">
            Kontak
        </button>
        <button type="button" onclick="openContacts(); toggleContactManager(true)" class="ag-btn-primary">
            Tambah Kontak
        </button>
    </div>

    <div id="contactOverlay" class="fixed inset-0 z-40 hidden bg-slate-950/50 backdrop-blur-sm md:hidden" onclick="closeContacts()"></div>

    <div class="relative flex h-[calc(100vh-220px)] min-h-[540px] overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm md:h-[660px]" id="chatApp">

        <!-- CONTACT LIST -->
        <div id="contactPanel" class="fixed inset-y-0 left-0 z-50 flex w-80 -translate-x-full flex-col border-r border-slate-200 bg-white shadow-2xl transition-transform duration-300 md:static md:z-auto md:w-80 md:translate-x-0 md:shadow-none">
            <div class="border-b border-slate-100 p-4">
                <div class="mb-3 flex items-center justify-between gap-3">
                    <h3 class="flex items-center gap-2 text-sm font-black text-emerald-700">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l3.586-3.586z"/></svg>
                    Kontak
                    </h3>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="toggleContactManager()" class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 transition hover:bg-emerald-100" aria-label="Tambah kontak">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6" /></svg>
                        </button>
                        <button type="button" onclick="closeContacts()" class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-slate-500 md:hidden" aria-label="Tutup kontak">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                </div>
                <div class="relative">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" placeholder="Cari kontak..." id="contactSearch" oninput="filterContacts()"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-9 pr-3 text-xs font-semibold outline-none transition-all focus:border-emerald-400">
                </div>
                <div id="contactManager" class="mt-3 hidden rounded-2xl border border-emerald-100 bg-emerald-50/50 p-3">
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-[11px] font-black uppercase tracking-wide text-emerald-800">Tambah Kontak</p>
                        <button type="button" onclick="toggleContactManager(false)" class="text-[11px] font-bold text-slate-500 hover:text-slate-700">Tutup</button>
                    </div>
                    <div class="mt-2 flex gap-2">
                        <input type="text" id="contactLookup" placeholder="Cari nama, email, wilayah..." class="min-w-0 flex-1 rounded-xl border border-emerald-100 bg-white px-3 py-2 text-xs font-semibold outline-none focus:border-emerald-400">
                        <button type="button" onclick="searchAvailableContacts()" class="rounded-xl bg-emerald-600 px-3 py-2 text-xs font-black text-white hover:bg-emerald-700">Cari</button>
                    </div>
                    <p class="mt-2 text-[10px] leading-relaxed text-slate-500">Kontak tidak lagi muncul otomatis. Tambahkan hanya user yang memang perlu diajak chat.</p>
                    <div id="contactLookupResults" class="mt-3 space-y-2"></div>
                </div>
            </div>
            <div id="contactSkeleton" class="p-4 space-y-3">
                @for ($i = 0; $i < 4; $i++)
                <div class="flex gap-3 animate-pulse">
                    <div class="w-10 h-10 rounded-full bg-gray-100 flex-shrink-0"></div>
                    <div class="flex-1"><div class="h-3 bg-gray-100 rounded w-2/3 mb-2"></div><div class="h-2.5 bg-gray-100 rounded w-full"></div></div>
                </div>
                @endfor
            </div>
            <div id="contactList" class="flex-1 overflow-y-auto hidden"></div>
        </div>

        <!-- CHAT AREA -->
        <div class="flex-1 flex flex-col" id="chatArea">
            <div id="chatPlaceholder" class="flex-1 flex flex-col items-center justify-center text-center p-8">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-7 h-7 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                </div>
                <p class="font-semibold text-gray-500 text-sm">Pilih kontak untuk mulai percakapan</p>
                <p class="text-xs text-gray-400 mt-1">Kontak chat sekarang bisa ditambah, diberi alias, dipin, atau dihapus.</p>
            </div>

            <div id="chatMain" class="flex-col flex-1 hidden" style="display:none">
                <div class="flex items-center gap-3 p-4 border-b border-gray-100 bg-gray-50/50">
                    <div id="chatHeaderAvatar" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full">?</div>
                    <div>
                        <p class="font-bold text-gray-800 text-sm" id="chatHeaderName">-</p>
                        <p class="text-[11px] text-gray-400" id="chatHeaderSub">-</p>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto p-4 space-y-3" id="chatMessages"></div>

                <div class="p-4 border-t border-gray-100">
                    <div class="flex gap-2 mb-3 flex-wrap" id="quickReplies">
                        <button onclick="quickReply('Siap dikirim hari ini')" class="qr-btn">Siap dikirim hari ini</button>
                        <button onclick="quickReply('Jadwal besok')" class="qr-btn">Jadwal besok</button>
                        <button onclick="quickReply('Sedang diproses, mohon tunggu')" class="qr-btn">Sedang diproses</button>
                        <button onclick="quickReply('Sudah tiba, konfirmasi penerimaan')" class="qr-btn">Sudah tiba</button>
                    </div>
                    <div class="flex gap-2 items-center">
                        <div class="flex-1 flex items-center bg-gray-50 border border-gray-200 rounded-2xl px-4 gap-2 focus-within:border-emerald-400 transition-all">
                            <input type="text" id="chatInput" placeholder="Tulis pesan..." class="flex-1 py-3 text-sm bg-transparent outline-none text-gray-700 placeholder-gray-400">
                        </div>
                        <button onclick="sendMsg()" class="w-11 h-11 bg-emerald-600 hover:bg-emerald-700 rounded-2xl flex items-center justify-center text-white transition-all shadow-md flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .qr-btn { font-size:.72rem; background:#f1f5f9; color:#475569; padding:7px 13px; border-radius:999px; border:1px solid #e2e8f0; cursor:pointer; transition:.2s; font-weight:700; }
        .qr-btn:hover { background:#047857; color:#fff; }
    </style>

    @push('scripts')
    <script>
        const CHAT_API_CONTACTS = '{{ route("api.chat.contacts") }}';
        const CHAT_API_CONTACT_SEARCH = '{{ route("api.chat.contacts.search") }}';
        const CHAT_API_CONTACT_STORE = '{{ route("api.chat.contacts.store") }}';
        const CHAT_API_CONTACT_UPDATE = '{{ route("api.chat.contacts.update", ['contact' => '__CONTACT__']) }}';
        const CHAT_API_CONTACT_DELETE = '{{ route("api.chat.contacts.destroy", ['contact' => '__CONTACT__']) }}';
        const CHAT_API_MESSAGES = '{{ route("api.chat.messages") }}';
        const CHAT_API_SEND = '{{ route("api.chat.send") }}';
        const CSRF_TOKEN = '{{ csrf_token() }}';
        const MY_ID = {{ auth()->id() }};
        const MY_NAME = @js(auth()->user()->name);
        const MY_PROFILE_PHOTO = @js(auth()->user()->profile_photo_url);
        const INITIAL_TARGET_ID = {{ (int) request('target', 0) }};
        let activeTarget = null;
        let pollInterval = null;
        let initialTargetHandled = false;
        let contactCache = [];
        const colors = ['#10b981', '#3b82f6', '#f59e0b', '#8b5cf6', '#fb923c', '#ef4444'];

        function openContacts() {
            document.getElementById('contactPanel')?.classList.remove('-translate-x-full');
            document.getElementById('contactOverlay')?.classList.remove('hidden');
        }

        function closeContacts() {
            document.getElementById('contactPanel')?.classList.add('-translate-x-full');
            document.getElementById('contactOverlay')?.classList.add('hidden');
        }

        function toggleContactManager(force) {
            const panel = document.getElementById('contactManager');
            if (!panel) return;

            const shouldOpen = typeof force === 'boolean' ? force : panel.classList.contains('hidden');
            panel.classList.toggle('hidden', !shouldOpen);

            if (shouldOpen) {
                setTimeout(() => document.getElementById('contactLookup')?.focus(), 50);
                if (!document.getElementById('contactLookupResults')?.innerHTML.trim()) {
                    searchAvailableContacts();
                }
            }
        }

        async function loadContacts() {
            try {
                const res = await fetch(CHAT_API_CONTACTS);
                const json = await res.json();
                document.getElementById('contactSkeleton').classList.add('hidden');
                document.getElementById('contactList').classList.remove('hidden');
                if (json.status !== 'success') throw new Error(json.msg);
                renderContacts(json.data || []);
                await openInitialTarget();
            } catch (e) {
                document.getElementById('contactSkeleton').classList.add('hidden');
                document.getElementById('contactList').classList.remove('hidden');
                document.getElementById('contactList').innerHTML = '<p class="text-xs text-gray-400 text-center p-6">Kontak chat belum bisa dimuat.</p>';
            }
        }

        function renderContacts(list) {
            const container = document.getElementById('contactList');
            contactCache = list || [];
            if (!list.length) {
                container.innerHTML = `<div class="p-6 text-center">
                    <p class="text-sm font-black text-slate-700">Belum ada kontak</p>
                    <p class="mt-1 text-xs leading-relaxed text-slate-400">Klik tombol tambah untuk memilih petani, pembeli, distributor, atau admin yang ingin diajak chat.</p>
                    <button type="button" onclick="toggleContactManager(true)" class="mt-4 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-black text-white hover:bg-emerald-700">Tambah Kontak</button>
                </div>`;
                return;
            }
            container.innerHTML = list.map((c, i) => {
                const color = colors[i % colors.length];
                const init = makeInitials(c.nama);
                const photo = c.profile_photo_url || '';
                const displayName = c.nama || c.nama_asli || 'Kontak';
                return `<div class="contact-item group flex items-center gap-3 p-4 hover:bg-gray-50 cursor-pointer border-b border-gray-50 transition-all"
                             data-id="${c.id}" data-contact-id="${c.contact_id}" data-name="${escAttr(displayName)}" data-real-name="${escAttr(c.nama_asli || displayName)}" data-label="${escAttr(c.label || '')}" data-pinned="${c.is_pinned ? '1' : '0'}" data-color="${escAttr(color)}" data-init="${escAttr(init)}" data-photo="${escAttr(photo)}" data-wilayah="${escAttr(c.wilayah || '-')}"
                             onclick="openChat(this)">
                    <div class="relative flex-shrink-0">
                        ${avatarMarkup({ name: displayName, init, photo, color, size: 'h-10 w-10', textSize: 'text-xs' })}
                        ${c.unread > 0 ? `<span class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white text-[9px] font-black rounded-full flex items-center justify-center">${c.unread}</span>` : ''}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-center">
                            <p class="font-bold text-gray-800 text-xs truncate">${c.is_pinned ? '<span class="text-amber-500">★</span> ' : ''}${escHtml(displayName)}</p>
                            <span class="text-[10px] text-gray-400">${c.last_time || ''}</span>
                        </div>
                        <p class="text-[11px] text-gray-400 truncate">${escHtml(c.last_msg || 'Belum ada pesan')}</p>
                    </div>
                    <div class="flex shrink-0 items-center gap-1 opacity-100 md:opacity-0 md:transition group-hover:opacity-100">
                        <button type="button" onclick="event.stopPropagation(); togglePinContact(${c.contact_id}, ${c.is_pinned ? 0 : 1})" class="flex h-7 w-7 items-center justify-center rounded-lg bg-slate-50 text-amber-500 hover:bg-amber-50" title="${c.is_pinned ? 'Lepas pin' : 'Pin kontak'}">
                            <svg class="h-3.5 w-3.5" fill="${c.is_pinned ? 'currentColor' : 'none'}" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 5 14 14M14 4l6 6-4 4-2 6-4-4-4 4 2-6-4-4 10-6z" /></svg>
                        </button>
                        <button type="button" onclick="event.stopPropagation(); editContactAlias(this.closest('.contact-item'))" class="flex h-7 w-7 items-center justify-center rounded-lg bg-slate-50 text-slate-500 hover:bg-slate-100" title="Ubah alias">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931z" /></svg>
                        </button>
                        <button type="button" onclick="event.stopPropagation(); deleteContact(${c.contact_id}, ${c.id})" class="flex h-7 w-7 items-center justify-center rounded-lg bg-red-50 text-red-500 hover:bg-red-100" title="Hapus kontak">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166M19.228 5.79 18.16 19.673A2.25 2.25 0 0 1 15.916 21H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .563c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                        </button>
                    </div>
                </div>`;
            }).join('');
        }

        async function openInitialTarget() {
            if (!INITIAL_TARGET_ID || initialTargetHandled) return;
            const target = document.querySelector(`.contact-item[data-id="${INITIAL_TARGET_ID}"]`);
            if (target) {
                initialTargetHandled = true;
                openChat(target);
                return;
            }

            initialTargetHandled = true;
            await addContactById(INITIAL_TARGET_ID, false);
            await loadContacts();
        }

        async function openChat(el) {
            closeContacts();
            document.querySelectorAll('.contact-item').forEach(c => c.classList.remove('bg-emerald-50', 'border-l-2', 'border-l-emerald-500'));
            el.classList.add('bg-emerald-50', 'border-l-2', 'border-l-emerald-500');

            activeTarget = { id: parseInt(el.dataset.id), name: el.dataset.name, color: el.dataset.color, init: el.dataset.init, photo: el.dataset.photo, sub: el.dataset.wilayah };

            document.getElementById('chatPlaceholder').style.display = 'none';
            const main = document.getElementById('chatMain');
            main.style.display = 'flex';
            main.classList.remove('hidden');

            const av = document.getElementById('chatHeaderAvatar');
            av.innerHTML = avatarMarkup({ name: activeTarget.name, init: activeTarget.init, photo: activeTarget.photo, color: activeTarget.color, size: 'h-9 w-9', textSize: 'text-xs' });
            document.getElementById('chatHeaderName').textContent = activeTarget.name;
            document.getElementById('chatHeaderSub').textContent = activeTarget.sub;

            await loadMessages();
            if (pollInterval) clearInterval(pollInterval);
            pollInterval = setInterval(loadMessages, 5000);
        }

        async function loadMessages() {
            if (!activeTarget) return;
            try {
                const res = await fetch(`${CHAT_API_MESSAGES}?target_user_id=${activeTarget.id}`);
                const json = await res.json();
                if (json.status !== 'success') throw new Error(json.msg);
                renderMessages(json.data || []);
            } catch (e) {}
        }

        function renderMessages(msgs) {
            const container = document.getElementById('chatMessages');
            container.innerHTML = `<div class="text-center"><span class="bg-gray-100 text-gray-400 text-[10px] px-3 py-1 rounded-full">Riwayat Pesan</span></div>`;
            msgs.forEach(m => {
                const isMe = parseInt(m.from_id) === MY_ID;
                const time = m.created_at ? new Date(m.created_at).toLocaleTimeString('id', { hour: '2-digit', minute: '2-digit' }) : '';
                if (isMe) {
                    container.innerHTML += `<div class="flex justify-end gap-2 items-end"><div class="max-w-[75%]"><div class="bg-emerald-600 text-white text-sm px-4 py-2.5 rounded-2xl rounded-tr-sm shadow-sm">${escHtml(m.pesan)}</div><p class="text-[10px] text-gray-300 text-right mt-1">${time}</p></div>${avatarMarkup({ name: MY_NAME, init: makeInitials(MY_NAME), photo: MY_PROFILE_PHOTO, color: '#047857', size: 'h-7 w-7', textSize: 'text-[10px]' })}</div>`;
                } else {
                    const senderPhoto = m.sender_photo_url || activeTarget?.photo || '';
                    const senderName = m.sender_name || activeTarget?.name || 'Kontak';
                    container.innerHTML += `<div class="flex gap-2 items-end">${avatarMarkup({ name: senderName, init: activeTarget?.init || makeInitials(senderName), photo: senderPhoto, color: activeTarget?.color || '#10b981', size: 'h-7 w-7', textSize: 'text-[10px]' })}<div class="max-w-[75%]"><div class="bg-gray-100 text-gray-800 text-sm px-4 py-2.5 rounded-2xl rounded-tl-sm">${escHtml(m.pesan)}</div><p class="text-[10px] text-gray-300 mt-1">${time}</p></div></div>`;
                }
            });
            container.scrollTop = container.scrollHeight;
        }

        async function sendMsg() {
            const inp = document.getElementById('chatInput');
            const txt = inp.value.trim();
            if (!txt || !activeTarget) return;
            inp.value = '';

            const container = document.getElementById('chatMessages');
            const time = new Date().toLocaleTimeString('id', { hour: '2-digit', minute: '2-digit' });
            container.innerHTML += `<div class="flex justify-end gap-2 items-end"><div class="max-w-[75%]"><div class="bg-emerald-600 text-white text-sm px-4 py-2.5 rounded-2xl rounded-tr-sm shadow-sm opacity-70">${escHtml(txt)}</div><p class="text-[10px] text-gray-300 text-right mt-1">${time}</p></div>${avatarMarkup({ name: MY_NAME, init: makeInitials(MY_NAME), photo: MY_PROFILE_PHOTO, color: '#047857', size: 'h-7 w-7', textSize: 'text-[10px]' })}</div>`;
            container.scrollTop = container.scrollHeight;

            try {
                const res = await fetch(CHAT_API_SEND, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                    body: JSON.stringify({ to_id: activeTarget.id, pesan: txt }),
                });
                const json = await res.json();
                if (json.status === 'success') { await loadMessages(); loadContacts(); }
            } catch (e) {}
        }

        async function searchAvailableContacts() {
            const q = document.getElementById('contactLookup')?.value || '';
            const container = document.getElementById('contactLookupResults');
            if (!container) return;

            container.innerHTML = '<p class="rounded-xl bg-white/70 px-3 py-2 text-[11px] font-semibold text-slate-400">Mencari kontak...</p>';

            try {
                const res = await fetch(`${CHAT_API_CONTACT_SEARCH}?q=${encodeURIComponent(q)}`, {
                    headers: { 'Accept': 'application/json' },
                });
                const json = await res.json();
                if (json.status !== 'success') throw new Error(json.message || 'Gagal mencari kontak');
                renderAvailableContacts(json.data || []);
            } catch (e) {
                container.innerHTML = '<p class="rounded-xl bg-white/70 px-3 py-2 text-[11px] font-semibold text-red-500">Kontak belum bisa dicari.</p>';
            }
        }

        function renderAvailableContacts(list) {
            const container = document.getElementById('contactLookupResults');
            if (!container) return;

            if (!list.length) {
                container.innerHTML = '<p class="rounded-xl bg-white/70 px-3 py-2 text-[11px] font-semibold text-slate-400">Tidak ada user yang cocok.</p>';
                return;
            }

            container.innerHTML = list.map((user, i) => {
                const color = colors[(i + 2) % colors.length];
                const init = makeInitials(user.nama);
                return `<div class="flex items-center gap-2 rounded-xl bg-white p-2 ring-1 ring-emerald-100">
                    ${avatarMarkup({ name: user.nama, init, photo: user.profile_photo_url || '', color, size: 'h-9 w-9', textSize: 'text-xs' })}
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-xs font-black text-slate-800">${escHtml(user.nama)}</p>
                        <p class="truncate text-[10px] font-semibold text-slate-400">${escHtml(user.role || 'User')} • ${escHtml(user.wilayah || '-')}</p>
                    </div>
                    <button type="button" ${user.already_added ? 'disabled' : ''} onclick="addContactById(${user.id})" class="rounded-lg px-3 py-2 text-[10px] font-black ${user.already_added ? 'bg-slate-100 text-slate-400' : 'bg-emerald-600 text-white hover:bg-emerald-700'}">
                        ${user.already_added ? 'Ada' : 'Tambah'}
                    </button>
                </div>`;
            }).join('');
        }

        async function addContactById(userId, reload = true) {
            try {
                const res = await fetch(CHAT_API_CONTACT_STORE, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                    body: JSON.stringify({ contact_user_id: userId }),
                });
                const json = await res.json();
                if (!res.ok || json.status !== 'success') throw new Error(json.message || 'Gagal menambah kontak');
                if (reload) {
                    await loadContacts();
                    await searchAvailableContacts();
                }
                return true;
            } catch (e) {
                alert(e.message || 'Kontak belum bisa ditambahkan.');
                return false;
            }
        }

        async function editContactAlias(el) {
            if (!el) return;
            const label = prompt('Masukkan alias kontak. Kosongkan untuk memakai nama asli.', el.dataset.label || '');
            if (label === null) return;
            await updateContact(el.dataset.contactId, { label });
        }

        async function togglePinContact(contactId, isPinned) {
            await updateContact(contactId, { is_pinned: Boolean(isPinned) });
        }

        async function updateContact(contactId, payload) {
            try {
                const res = await fetch(contactCrudUrl(CHAT_API_CONTACT_UPDATE, contactId), {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                    body: JSON.stringify(payload),
                });
                const json = await res.json();
                if (!res.ok || json.status !== 'success') throw new Error(json.message || 'Gagal memperbarui kontak');
                await loadContacts();
            } catch (e) {
                alert(e.message || 'Kontak belum bisa diperbarui.');
            }
        }

        async function deleteContact(contactId, userId) {
            if (!confirm('Hapus kontak ini dari daftar chat? Riwayat pesan tidak akan dihapus.')) return;

            try {
                const res = await fetch(contactCrudUrl(CHAT_API_CONTACT_DELETE, contactId), {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                });
                const json = await res.json();
                if (!res.ok || json.status !== 'success') throw new Error(json.message || 'Gagal menghapus kontak');

                if (activeTarget?.id === parseInt(userId)) {
                    activeTarget = null;
                    if (pollInterval) clearInterval(pollInterval);
                    document.getElementById('chatMain').style.display = 'none';
                    document.getElementById('chatMain').classList.add('hidden');
                    document.getElementById('chatPlaceholder').style.display = 'flex';
                }

                await loadContacts();
                await searchAvailableContacts();
            } catch (e) {
                alert(e.message || 'Kontak belum bisa dihapus.');
            }
        }

        function contactCrudUrl(template, contactId) {
            return template.replace('__CONTACT__', encodeURIComponent(contactId));
        }

        function quickReply(txt) {
            document.getElementById('chatInput').value = txt;
            document.getElementById('chatInput').focus();
        }

        function filterContacts() {
            const q = document.getElementById('contactSearch').value.toLowerCase();
            document.querySelectorAll('.contact-item').forEach(c => {
                c.style.display = c.dataset.name.toLowerCase().includes(q) ? '' : 'none';
            });
        }

        function avatarMarkup({ name = 'Pengguna', init = '', photo = '', color = '#10b981', size = 'h-10 w-10', textSize = 'text-xs' } = {}) {
            const safeName = escAttr(name || 'Pengguna');
            const safeInit = escHtml(init || makeInitials(name));
            const safeColor = escAttr(color || '#10b981');
            const fallback = `<span class="${size} flex items-center justify-center rounded-full font-bold text-white ${textSize}" style="background:${safeColor}">${safeInit}</span>`;

            if (!photo) {
                return fallback;
            }

            return `<span class="${size} relative block overflow-hidden rounded-full bg-slate-100 ring-1 ring-slate-200">
                <img src="${escAttr(photo)}" alt="Foto profil ${safeName}" class="h-full w-full object-cover" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                <span class="absolute inset-0 hidden items-center justify-center rounded-full font-bold text-white ${textSize}" style="background:${safeColor}">${safeInit}</span>
            </span>`;
        }

        function makeInitials(name) {
            return String(name || '?')
                .trim()
                .split(/\s+/)
                .map(word => word.charAt(0))
                .join('')
                .slice(0, 2)
                .toUpperCase() || '?';
        }

        function escHtml(s) {
            if (!s) return '';
            return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        }

        function escAttr(s) {
            return escHtml(s).replace(/"/g, '&quot;').replace(/'/g, '&#39;');
        }

        document.getElementById('chatInput')?.addEventListener('keydown', e => { if (e.key === 'Enter') sendMsg(); });
        document.getElementById('contactLookup')?.addEventListener('keydown', e => { if (e.key === 'Enter') searchAvailableContacts(); });
        loadContacts();
    </script>
    @endpush
</x-layouts.app>

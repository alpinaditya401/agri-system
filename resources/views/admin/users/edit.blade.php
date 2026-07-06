<x-layouts.app :title="'Edit Pengguna – Agrilink'">
    <x-slot:sidebar>
        @include('admin._sidebar')
    </x-slot:sidebar>

    <x-slot:header>
        <h1 class="text-lg font-bold text-gray-800">Edit Pengguna</h1>
        <p class="text-gray-500 text-sm">Perbarui informasi akun "{{ $user->name }}".</p>
    </x-slot:header>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 max-w-2xl">
        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="text-xs font-semibold text-gray-500">Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                       class="w-full mt-1 px-3 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:border-emerald-400">
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                       class="w-full mt-1 px-3 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:border-emerald-400">
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500">Password Baru (kosongkan jika tidak diubah)</label>
                <input type="password" name="password" minlength="8"
                       class="w-full mt-1 px-3 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:border-emerald-400">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-semibold text-gray-500">Role</label>
                    <select name="role_id" required class="w-full mt-1 px-3 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:border-emerald-400">
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>{{ $role->display_name ?? $role->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500">No. Telepon</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" inputmode="numeric" pattern="[0-9]{10,15}" maxlength="15" data-digits-only placeholder="08xxxxxxxxxx"
                           class="w-full mt-1 px-3 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:border-emerald-400">
                    @error('phone') <p class="mt-1 text-xs font-bold text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
            <label class="flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" name="is_active" value="1" {{ $user->is_active ? 'checked' : '' }} class="rounded text-emerald-600 focus:ring-emerald-400">
                Akun aktif
            </label>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-5 py-2.5 rounded-xl text-sm transition-colors">Simpan Perubahan</button>
                <a href="{{ route('admin.users.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 font-semibold px-5 py-2.5 rounded-xl text-sm transition-colors">Batal</a>
            </div>
        </form>
    </div>
</x-layouts.app>

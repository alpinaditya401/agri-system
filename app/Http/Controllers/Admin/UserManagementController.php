<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(): View
    {
        $users = User::with('role')->latest()->paginate(25);
        $roles = $this->availableRolesFor(auth()->user());

        return view('admin.users.index', compact('users', 'roles'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): View
    {
        $roles = $this->availableRolesFor(auth()->user());
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->has('phone')) {
            $phone = trim((string) $request->input('phone'));
            $request->merge(['phone' => $phone === '' ? null : $phone]);
        }

        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role_id'  => ['required', 'exists:roles,id'],
            'phone'    => ['nullable', 'string', 'regex:/^[0-9]{10,15}$/'],
            'is_active'=> ['boolean'],
        ], [
            'phone.regex' => 'Nomor HP hanya boleh angka 10-15 digit.',
        ]);

        if (!$this->roleCanBeAssigned($request->user(), (int) $validated['role_id'])) {
            return back()->withInput()->with('error', 'Hanya Admin Master yang dapat membuat akun Admin atau Admin Master.');
        }

        User::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'role_id'   => $validated['role_id'],
            'phone'     => $validated['phone'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil dibuat.');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): View
    {
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user): View
    {
        $this->abortIfCannotManage($user);

        $roles = $this->availableRolesFor(auth()->user());
        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        if ($request->has('phone')) {
            $phone = trim((string) $request->input('phone'));
            $request->merge(['phone' => $phone === '' ? null : $phone]);
        }

        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', "unique:users,email,{$user->id}"],
            'role_id'  => ['required', 'exists:roles,id'],
            'phone'    => ['nullable', 'string', 'regex:/^[0-9]{10,15}$/'],
            'is_active'=> ['boolean'],
        ], [
            'phone.regex' => 'Nomor HP hanya boleh angka 10-15 digit.',
        ]);

        if (!$this->canManageUser($request->user(), $user)) {
            return back()->with('error', 'Admin biasa tidak dapat mengontrol akun Admin atau Admin Master.');
        }

        if (!$this->roleCanBeAssigned($request->user(), (int) $validated['role_id'])) {
            return back()->withInput()->with('error', 'Hanya Admin Master yang dapat menetapkan role Admin atau Admin Master.');
        }

        $user->update([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'role_id'   => $validated['role_id'],
            'phone'     => $validated['phone'] ?? null,
            'is_active' => $validated['is_active'] ?? $user->is_active,
        ]);

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->input('password'))]);
        }

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil diperbarui.');
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user): RedirectResponse
    {
        if (!$this->canManageUser(request()->user(), $user)) {
            return back()->with('error', 'Admin biasa tidak dapat menghapus akun Admin atau Admin Master.');
        }

        if ($user->id === request()->user()->id) {
            return back()->with('error', 'Tidak dapat menghapus akun yang sedang digunakan.');
        }

        if ($user->isAdminMaster()) {
            return back()->with('error', 'Akun Admin Master tidak dapat dihapus.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil dihapus.');
    }

    /**
     * Toggle user active status.
     */
    public function toggleActive(User $user): RedirectResponse
    {
        if (!$this->canManageUser(request()->user(), $user)) {
            return back()->with('error', 'Admin biasa tidak dapat mengubah status akun Admin atau Admin Master.');
        }

        if ($user->id === request()->user()->id) {
            return back()->with('error', 'Tidak dapat mengubah status akun yang sedang digunakan.');
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Pengguna {$user->name} telah {$status}.");
    }

    private function availableRolesFor(User $actor)
    {
        return Role::query()
            ->when(
                !$actor->isAdminMaster(),
                fn($query) => $query->whereNotIn('name', ['admin', 'admin_master'])
            )
            ->orderBy('display_name')
            ->get();
    }

    private function roleCanBeAssigned(User $actor, int $roleId): bool
    {
        $roleName = Role::whereKey($roleId)->value('name');

        if (!$roleName) {
            return false;
        }

        return $actor->isAdminMaster() || !in_array($roleName, ['admin', 'admin_master'], true);
    }

    private function canManageUser(User $actor, User $target): bool
    {
        if ($actor->isAdminMaster()) {
            return !$target->isAdminMaster() && $actor->id !== $target->id;
        }

        return !$target->isAdmin() && !$target->isAdminMaster();
    }

    private function abortIfCannotManage(User $target): void
    {
        abort_unless(
            $this->canManageUser(auth()->user(), $target),
            403,
            'Admin biasa tidak dapat mengontrol akun Admin atau Admin Master.'
        );
    }
}

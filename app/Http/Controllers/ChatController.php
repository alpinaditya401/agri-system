<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\ChatContact;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ChatController extends Controller
{
    /**
     * Render the chat page (sidebar/layout aware — works for every role).
     */
    public function index(): View
    {
        $target = (int) request('target', 0);

        if ($target && $target !== auth()->id()) {
            $exists = User::whereKey($target)
                ->where('is_active', true)
                ->exists();

            if ($exists) {
                $this->ensureContact(auth()->id(), $target);
            }
        }

        return view('chat.index');
    }

    /**
     * Open a direct chat from another workflow, such as an order or product page.
     */
    public function initiateChat(int $seller_id): RedirectResponse
    {
        abort_if($seller_id === auth()->id(), 403);

        User::whereKey($seller_id)
            ->where('is_active', true)
            ->firstOrFail();

        $this->ensureContact(auth()->id(), $seller_id);

        return redirect()->route('chat.index', ['target' => $seller_id]);
    }

    /**
     * List of contacts the current user can chat with.
     * Any active user can chat with another active user.
     */
    public function contacts(): JsonResponse
    {
        $me = auth()->user();

        $contacts = ChatContact::with(['contact.role', 'contact.farmerProfile', 'contact.distributorProfile'])
            ->where('user_id', $me->id)
            ->whereHas('contact', fn($query) => $query->where('is_active', true))
            ->orderByDesc('is_pinned')
            ->latest('updated_at')
            ->limit(50)
            ->get()
            ->map(function (ChatContact $chatContact) use ($me) {
            $user = $chatContact->contact;
            $lastMessage = ChatMessage::between($me->id, $user->id)->latest()->first();
            $unread = ChatMessage::where('from_id', $user->id)
                ->where('to_id', $me->id)
                ->where('is_read', false)
                ->count();

            return [
                'contact_id'        => $chatContact->id,
                'id'                => $user->id,
                'nama'              => $chatContact->label ?: $user->name,
                'nama_asli'         => $user->name,
                'wilayah'           => $user->district ?? $user->village ?? $user->role?->display_name,
                'profile_photo_url' => $user->profile_photo_url,
                'label'             => $chatContact->label,
                'is_pinned'         => $chatContact->is_pinned,
                'unread'            => $unread,
                'last_msg'          => $lastMessage?->pesan,
                'last_time'         => $lastMessage?->created_at?->diffForHumans(),
            ];
        })->sort(function ($a, $b) {
            return [
                $b['is_pinned'] ? 1 : 0,
                $b['unread'],
            ] <=> [
                $a['is_pinned'] ? 1 : 0,
                $a['unread'],
            ];
        })->values();

        return response()->json(['status' => 'success', 'data' => $contacts]);
    }

    /**
     * Search active users that may be added to the current chat contacts.
     */
    public function availableContacts(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:80'],
        ]);

        $me = auth()->user();
        $query = User::with('role')
            ->where('id', '!=', $me->id)
            ->where('is_active', true);

        if (! empty($validated['q'])) {
            $keyword = $validated['q'];
            $query->where(function ($builder) use ($keyword) {
                $builder->where('name', 'like', '%' . $keyword . '%')
                    ->orWhere('email', 'like', '%' . $keyword . '%')
                    ->orWhere('phone', 'like', '%' . $keyword . '%')
                    ->orWhere('district', 'like', '%' . $keyword . '%')
                    ->orWhereHas('role', fn($role) => $role->where('display_name', 'like', '%' . $keyword . '%'));
            });
        }

        $existingIds = ChatContact::where('user_id', $me->id)
            ->pluck('contact_user_id')
            ->all();

        $users = $query->orderBy('name')
            ->limit(12)
            ->get()
            ->map(fn(User $user) => [
                'id'                => $user->id,
                'nama'              => $user->name,
                'wilayah'           => $user->district ?? $user->village ?? $user->role?->display_name,
                'role'              => $user->role?->display_name ?? $user->role?->name,
                'profile_photo_url' => $user->profile_photo_url,
                'already_added'     => in_array($user->id, $existingIds, true),
            ]);

        return response()->json(['status' => 'success', 'data' => $users]);
    }

    /**
     * Add a user into the current user's chat contacts.
     */
    public function storeContact(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'contact_user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where('is_active', true),
            ],
            'label' => ['nullable', 'string', 'max:80'],
            'is_pinned' => ['nullable', 'boolean'],
        ]);

        abort_if((int) $validated['contact_user_id'] === auth()->id(), 422, 'Tidak bisa menambahkan diri sendiri sebagai kontak.');

        $contact = $this->ensureContact(
            auth()->id(),
            (int) $validated['contact_user_id'],
            [
                'label' => $validated['label'] ?? null,
                'is_pinned' => (bool) ($validated['is_pinned'] ?? false),
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Kontak chat berhasil ditambahkan.',
            'data' => $contact,
        ], 201);
    }

    /**
     * Update label or pin status for a saved chat contact.
     */
    public function updateContact(Request $request, ChatContact $contact): JsonResponse
    {
        abort_if($contact->user_id !== auth()->id(), 403);

        $validated = $request->validate([
            'label' => ['nullable', 'string', 'max:80'],
            'is_pinned' => ['nullable', 'boolean'],
        ]);

        if (array_key_exists('label', $validated)) {
            $contact->label = filled($validated['label'] ?? null) ? trim($validated['label']) : null;
        }

        if (array_key_exists('is_pinned', $validated)) {
            $contact->is_pinned = (bool) $validated['is_pinned'];
        }

        $contact->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Kontak chat berhasil diperbarui.',
            'data' => $contact,
        ]);
    }

    /**
     * Remove a contact from the current user's chat list.
     * Message history is preserved.
     */
    public function destroyContact(ChatContact $contact): JsonResponse
    {
        abort_if($contact->user_id !== auth()->id(), 403);

        $contact->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Kontak chat dihapus dari daftar.',
        ]);
    }

    /**
     * Fetch messages between the authenticated user and a target user.
     */
    public function messages(Request $request): JsonResponse
    {
        $request->validate(['target_user_id' => ['required', 'exists:users,id']]);

        $me = auth()->id();
        $target = (int) $request->input('target_user_id');
        abort_if($target === $me, 422);

        User::whereKey($target)
            ->where('is_active', true)
            ->firstOrFail();

        $hasContact = ChatContact::where('user_id', $me)
            ->where('contact_user_id', $target)
            ->exists();
        $hasHistory = ChatMessage::between($me, $target)->exists();

        abort_if(! $hasContact && ! $hasHistory, 403, 'Tambahkan kontak terlebih dahulu sebelum membuka percakapan.');

        $this->ensureContact($me, $target, ['last_opened_at' => now()]);

        $messages = ChatMessage::with('sender')
            ->between($me, $target)
            ->orderBy('created_at')
            ->limit(200)
            ->get()
            ->map(fn($m) => [
                'from_id'     => $m->from_id,
                'sender_name' => $m->sender?->name,
                'sender_photo_url' => $m->sender?->profile_photo_url,
                'pesan'       => $m->pesan,
                'created_at'  => $m->created_at,
            ]);

        // Mark incoming messages as read
        ChatMessage::where('from_id', $target)
            ->where('to_id', $me)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return response()->json(['status' => 'success', 'data' => $messages]);
    }

    /**
     * Send a chat message and push a notification to the recipient.
     */
    public function send(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'to_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where('is_active', true),
            ],
            'pesan' => ['required', 'string', 'max:2000'],
        ]);

        abort_if((int) $validated['to_id'] === auth()->id(), 422);

        $this->ensureContact(auth()->id(), (int) $validated['to_id'], ['last_opened_at' => now()]);
        $this->ensureContact((int) $validated['to_id'], auth()->id());

        $message = ChatMessage::create([
            'from_id' => auth()->id(),
            'to_id'   => $validated['to_id'],
            'pesan'   => $validated['pesan'],
        ]);

        Notification::sendToUser(
            userId: $validated['to_id'],
            tipe: 'chat',
            judul: 'Pesan baru dari ' . auth()->user()->name,
            pesan: \Illuminate\Support\Str::limit($validated['pesan'], 80),
            link: route('chat.index'),
        );

        return response()->json(['status' => 'success', 'data' => $message]);
    }

    private function ensureContact(int $ownerId, int $contactUserId, array $attributes = []): ?ChatContact
    {
        if ($ownerId === $contactUserId) {
            return null;
        }

        $contact = ChatContact::firstOrNew([
            'user_id' => $ownerId,
            'contact_user_id' => $contactUserId,
        ]);

        foreach ($attributes as $key => $value) {
            if ($key === 'label' && ! filled($value)) {
                $value = null;
            }

            $contact->{$key} = $value;
        }

        $contact->save();

        return $contact;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'tipe', 'judul', 'pesan', 'link', 'dibaca', 'read_at',
    ];

    protected $casts = [
        'dibaca'  => 'boolean',
        'read_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUnread($query)
    {
        return $query->where('dibaca', false);
    }

    /**
     * Helper to create an in-app notification for a user.
     */
    public static function sendToUser(int $userId, string $tipe, string $judul, string $pesan, ?string $link = null): self
    {
        return self::create([
            'user_id' => $userId,
            'tipe'    => $tipe,
            'judul'   => $judul,
            'pesan'   => $pesan,
            'link'    => $link,
        ]);
    }

    public static function sendToAdmins(string $tipe, string $judul, string $pesan, ?string $link = null): void
    {
        User::query()
            ->whereHas('role', fn($query) => $query->whereIn('name', ['admin', 'admin_master']))
            ->where('is_active', true)
            ->pluck('id')
            ->each(fn(int $userId) => self::sendToUser($userId, $tipe, $judul, $pesan, $link));
    }
}

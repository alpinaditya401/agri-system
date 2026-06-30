<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_id', 'to_id', 'pesan', 'is_read', 'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'from_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'to_id');
    }

    /**
     * Scope: conversation between two specific users (either direction).
     */
    public function scopeBetween($query, int $userA, int $userB)
    {
        return $query->where(function ($q) use ($userA, $userB) {
            $q->where('from_id', $userA)->where('to_id', $userB);
        })->orWhere(function ($q) use ($userA, $userB) {
            $q->where('from_id', $userB)->where('to_id', $userA);
        });
    }
}

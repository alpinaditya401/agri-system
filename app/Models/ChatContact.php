<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'contact_user_id',
        'label',
        'is_pinned',
        'last_opened_at',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'last_opened_at' => 'datetime',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function contact()
    {
        return $this->belongsTo(User::class, 'contact_user_id');
    }
}

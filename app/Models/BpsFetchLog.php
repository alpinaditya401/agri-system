<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BpsFetchLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'endpoint', 'status', 'records_fetched',
        'error_message', 'response_time_ms', 'fetched_at',
    ];

    protected $casts = [
        'fetched_at' => 'datetime',
    ];
}

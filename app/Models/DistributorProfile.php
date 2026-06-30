<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DistributorProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_name',
        'license_number',
        'storage_capacity_kg',
        'verification_status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

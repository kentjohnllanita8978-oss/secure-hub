<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginEvent extends Model
{
    protected $fillable = [
        'integration_id', 'user_id', 'ip_address', 'device',
        'status', 'is_first_login', 'credential_username',
    ];

    protected $casts = [
        'is_first_login' => 'boolean',
    ];

    public function integration(): BelongsTo
    {
        return $this->belongsTo(Integration::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace VictorYoalli\MultitenancyImpersonate\Tests\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TestableImpersonateToken extends Model
{
    protected $table = 'impersonate_tokens';

    protected $guarded = [];

    protected $casts = [
        'expired_at' => 'datetime',
        'impersonated_at' => 'datetime',
    ];

    public function scopeLive(Builder $query): Builder
    {
        return $query->where('expired_at', '>', now())->whereNull('impersonated_at');
    }

    public function markAsUsed(): bool
    {
        $this->impersonated_at = now();
        $this->ip_address = request()->ip();
        $this->user_id = auth()->id();

        return $this->save();
    }
}

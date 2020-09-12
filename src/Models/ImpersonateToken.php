<?php

namespace VictorYoalli\MultitenancyImpersonate\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class ImpersonateToken extends Model
{
    use UsesTenantConnection;

    protected $guarded = [];

    public function scopeLive($query)
    {
        return $query->where('expired_at', '>', now())->whereNull('impersonated_at');
    }

    public function touch():void
    {
        $this->impersonated_at = now();
        $this->ip_address = request()->ip();
        $this->user_id = auth()->id();
        $this->save();
    }
}

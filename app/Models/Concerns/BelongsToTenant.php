<?php

namespace App\Models\Concerns;

use App\Models\Tenant;
use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            $context = app(TenantContext::class);

            if ($context->hasTenant()) {
                $builder->where($builder->getModel()->qualifyColumn('tenant_id'), $context->id());
            }
        });

        static::creating(function ($model) {
            $context = app(TenantContext::class);

            if ($context->hasTenant() && $model->tenant_id === null) {
                $model->tenant_id = $context->id();
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty('tenant_id')) {
                throw new \RuntimeException('tenant_id tidak boleh diubah setelah dibuat.');
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}

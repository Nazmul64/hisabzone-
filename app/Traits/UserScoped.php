<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait UserScoped
{
    // ── Boot: auto-inject user_id on create & apply global scope ──

    protected static function bootUserScoped(): void
    {
        // Auto-inject user_id when creating
        static::creating(function ($model) {
            if (auth()->check() && empty($model->user_id)) {
                $model->user_id = auth()->id();
            }
        });

        // Global scope: only return current user's records
        static::addGlobalScope('user', function (Builder $builder) {
            if (auth()->check()) {
                $builder->where(
                    $builder->getModel()->getTable() . '.user_id',
                    auth()->id()
                );
            }
        });
    }
}

<?php

namespace App\Models\Concerns;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function (Model $model) {
            self::writeAudit('model.created', $model, null, $model->getAttributes());
        });

        static::updated(function (Model $model) {
            $dirty = $model->getChanges();
            $original = array_intersect_key($model->getOriginal(), $dirty);
            self::writeAudit('model.updated', $model, $original, $dirty);
        });

        static::deleted(function (Model $model) {
            self::writeAudit('model.deleted', $model, $model->getOriginal(), null);
        });
    }

    protected static function writeAudit(string $event, Model $model, ?array $oldValues, ?array $newValues): void
    {
        // Avoid flooding logs during seeding/migrations.
        if (app()->runningInConsole()) {
            return;
        }

        // Don't recurse if we ever audit AuditLog itself.
        if ($model instanceof AuditLog) {
            return;
        }

        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => $event,
            'action' => null,
            'model_type' => $model::class,
            'model_id' => $model->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
        ]);
    }
}


<?php

namespace App\Traits;

use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Model;

trait Auditable
{
    /**
     * Boot the Auditable trait.
     */
    protected static function bootAuditable(): void
    {
        static::created(function (Model $model) {
            AuditLogService::log(
                'CREATE',
                class_basename($model) . ' created',
                $model,
                null,
                $model->getAttributes()
            );
        });

        static::updated(function (Model $model) {
            // Only log if there are changes
            if (empty($model->getChanges())) {
                return;
            }

            // Get changed attributes
            $changes = $model->getChanges();
            $original = $model->getOriginal();
            
            // Filter original to only include changed keys
            $oldValues = array_intersect_key($original, $changes);

            // Exclude timestamps from noise if desired, 
            // but usually good to keep if they are explicitly changed aside from autoupdate
            // For now, we take getChanges() which includes updated_at usually.

            AuditLogService::log(
                'UPDATE',
                class_basename($model) . ' updated',
                $model,
                $oldValues,
                $changes
            );
        });

        static::deleted(function (Model $model) {
            AuditLogService::log(
                'DELETE',
                class_basename($model) . ' deleted',
                $model,
                $model->getAttributes(),
                null
            );
        });
    }
}

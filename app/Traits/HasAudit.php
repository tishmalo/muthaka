<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

trait HasAudit
{
    protected static function bootHasAudit()
    {
        static::created(function ($model) {
            $model->logAudit('created', null, $model->getAttributes());
        });

        static::updated(function ($model) {
            $model->logAudit('updated', $model->getOriginal(), $model->getChanges());
        });

        static::deleted(function ($model) {
            $model->logAudit('deleted', $model->getAttributes(), null);
        });
    }

    public function logAudit(string $action, ?array $oldValues = null, ?array $newValues = null): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'resource_type' => $this->getMorphClass(),
            'resource_id' => $this->getKey(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'metadata' => [
                'url' => request()->fullUrl(),
                'method' => request()->method(),
            ],
            'created_at' => now(),
        ]);
    }

    public function auditLogs()
    {
        return $this->morphMany(AuditLog::class, 'resource');
    }
}
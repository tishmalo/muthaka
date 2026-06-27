<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\SoftDeletes;

trait HasSoftDeletesOverride
{
    use SoftDeletes;

    public static function bootHasSoftDeletesOverride()
    {
        static::forceDeleting(function ($model) {
            $model->beforeForceDelete();
        });
    }

    protected function beforeForceDelete(): void
    {
        // Override in child classes to clean up related data
    }

    public function restore(): bool
    {
        $this->restoreRelated();
        return parent::restore();
    }

    protected function restoreRelated(): void
    {
        // Override in child classes to restore related models
    }
}
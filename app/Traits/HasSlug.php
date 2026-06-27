<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasSlug
{
    public static function bootHasSlug()
    {
        static::creating(function ($model) {
            $model->generateSlug();
        });

        static::updating(function ($model) {
            if ($model->isDirty('name') || $model->isDirty('title')) {
                $model->generateSlug();
            }
        });
    }

    public function generateSlug(): void
    {
        $slugField = $this->slugField ?? 'slug';
        $sourceField = $this->slugSource ?? 'name';
        
        if (empty($this->{$slugField})) {
            $slug = Str::slug($this->{$sourceField});
            $originalSlug = $slug;
            $counter = 1;

            while ($this->slugExists($slug, $slugField)) {
                $slug = $originalSlug . '-' . $counter++;
            }

            $this->{$slugField} = $slug;
        }
    }

    protected function slugExists(string $slug, string $field): bool
    {
        $query = static::where($field, $slug);
        
        if ($this->exists) {
            $query->where('id', '!=', $this->id);
        }

        return $query->exists();
    }
}
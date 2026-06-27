<?php

namespace App\Traits;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasSettings
{
    public function settings(): MorphMany
    {
        return $this->morphMany(Setting::class, 'settingable');
    }

    public function getSetting(string $key, $default = null)
    {
        $setting = $this->settings()->where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public function setSetting(string $key, $value, string $group = 'general', bool $isPublic = false): void
    {
        $this->settings()->updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'group' => $group,
                'is_public' => $isPublic,
            ]
        );
    }

    public function getSettingsGroup(string $group): array
    {
        return $this->settings()
            ->where('group', $group)
            ->pluck('value', 'key')
            ->toArray();
    }

    public function deleteSetting(string $key): void
    {
        $this->settings()->where('key', $key)->delete();
    }
}
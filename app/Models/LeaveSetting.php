<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveSetting extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'key',
        'value',
        'type',
        'category',
        'description',
    ];

    /**
     * Get a setting value by key
     */
    public static function get($key, $default = null)
    {
        $setting = static::where('key', $key)->first();

        if (!$setting) {
            return $default;
        }

        return static::castValue($setting->value, $setting->type);
    }

    /**
     * Set a setting value
     */
    public static function set($key, $value)
    {
        $setting = static::where('key', $key)->first();

        if (!$setting) {
            return static::create([
                'key' => $key,
                'value' => is_array($value) ? json_encode($value) : $value,
                'type' => is_array($value) ? 'json' : (is_bool($value) ? 'boolean' : 'string'),
            ]);
        }

        $setting->update([
            'value' => is_array($value) ? json_encode($value) : $value,
        ]);

        return $setting;
    }

    /**
     * Cast value based on type
     */
    protected static function castValue($value, $type)
    {
        switch ($type) {
            case 'boolean':
                return (bool) $value;
            case 'integer':
                return (int) $value;
            case 'json':
                return json_decode($value, true);
            default:
                return $value;
        }
    }

    /**
     * Get all settings by category
     */
    public static function getByCategory($category)
    {
        return static::where('category', $category)->get()->mapWithKeys(function ($setting) {
            return [$setting->key => static::castValue($setting->value, $setting->type)];
        });
    }
}

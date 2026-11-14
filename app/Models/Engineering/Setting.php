<?php

namespace App\Models\Engineering;

use Illuminate\Database\Eloquent\Model;

/**
 * Runtime override of a config/engineering.php value, keyed by its dot path.
 */
class Setting extends Model
{
    protected $table = 'eng_settings';

    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected $casts = ['value' => 'array'];

    /** The stored override, or the config default when none has been saved. */
    public static function read(string $key): mixed
    {
        return static::find($key)?->value ?? config('engineering.'.$key);
    }

    public static function write(string $key, mixed $value, ?string $userId = null): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value, 'updated_by' => $userId]);
    }
}

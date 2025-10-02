<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    use HasFactory, HasUuids, LogsActivity, SoftDeletes;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * IDs are non-incrementing (UUID).
     */
    public $incrementing = false;

    /**
     * The primary key type is string (UUID).
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * All attributes are mass assignable.
     *
     * @var array<int,string>
     */
    protected $guarded = [];

    //    /**
    //     * Boot the model and generate a UUID on create.
    //     */
    //    protected static function boot(): void
    //    {
    //        parent::boot();
    //
    //        static::creating(function (self $model): void {
    //            if (empty($model->{$model->getKeyName()})) {
    //                $model->{$model->getKeyName()} = Str::uuid()->toString();
    //            }
    //        });
    //    }

    /**
     * Configure activity log options for this model.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            // Only log these attributes when they change
            ->logOnly(['name', 'guard_name', 'description', 'category_id'])
            // Only record changed (dirty) attributes
            ->logOnlyDirty()
            // Use a custom log name to group permission changes
            ->useLogName('permission');
    }

    /**
     * A permission belongs to a permission category.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(PermissionCategory::class, 'category_id');
    }
}

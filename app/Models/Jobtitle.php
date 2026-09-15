<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Jobtitle extends Model
{
    use HasFactory;     // Enables model factories for testing and seeding
    use HasUuids;       // Adds support for soft deletes via `deleted_at` column
    use LogsActivity;    // Uses UUIDs instead of auto-incrementing IDs
    use SoftDeletes;   // Logs model changes to the activity_log table

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $table = 'jobtitles';
    protected $fillable = [
        'name',
        'code',
        'description',
        'added_by',
    ];

    /**
     * Configure the activity log options for this model.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            // only record changes to the "name" attribute
            ->logOnly(['name'])
            // set a custom log name (optional)
            ->useLogName('jobtitles')
            // don't submit empty logs when nothing changed
            ->logOnlyDirty();
    }

    /**
     * Get the users associated with this job title.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class, 'title_id');
    }
}

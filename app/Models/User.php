<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Spatie\Activitylog\LogOptions;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;
    use HasRoles;
    use HasUuids;
    use LogsActivity;
    use SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'salutation',
        'first_name',
        'middle_name',
        'surname',
        'gender',
        'dob',
        'address',
        'district',
        'region',
        'country',
        'postal_code',
        'email',
        'phone_number',
        'username',
        'password',
        'provider_type',
        'reg_number',
        'qualification',
        'profile_picture',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'dob' => 'date',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('user')
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * @property string|null $salutation
     * @property string|null $first_name
     * @property string|null $middle_name
     * @property string|null $surname
     */
    public function getFullNameAttribute(): string
    {
        return collect([
            $this->salutation,
            $this->first_name,
            $this->middle_name,
            $this->surname,
        ])
            ->filter()
            ->implode(' ');
    }

    /**
     * @property string|null $profile_picture;
     */
    public function getProfilePictureUrlAttribute(): ?string
    {
        return $this->profile_picture
            ? asset('storage/'.$this->profile_picture)
            : null;
    }

    // public function loginActivities(): HasMany
    // {
    //     return $this->hasMany(LoginActivity::class);
    // }

    public function jobtitle(): BelongsTo
    {
        return $this->belongsTo(Jobtitle::class);
    }

    /**
     * Get the locations associated with this user.
     */
    // public function locations(): BelongsToMany
    // {
    //     return $this->belongsToMany(Location::class, 'user_locations')
    //         ->using(UserLocation::class)
    //         ->withTimestamps();
    // }

    /**
     * Get the visitations initiated by this user.
     */
    // public function initiatedVisitations(): HasMany
    // {
    //     return $this->hasMany(Visitation::class, 'initiator_id');
    // }
}

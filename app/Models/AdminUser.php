<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

/**
 * Class AdminUser
 *
 * Represents an administrative user in the system.
 *
 * @package App\Models
 *
 * @property int $id
 * @property string $username
 * @property string|null $email
 * @property string|null $full_name
 * @property string|null $password
 * @property string|null $avatar_url
 * @property string|null $phone
 * @property int|null $primary_unit_id
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $last_login_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Role[] $roles
 * @property-read \App\Models\OrgUnit|null $unit
 * @property-read string|null $role Primary role code.
 * @property-read array $role_codes List of all role codes.
 * @property-read array $permission_codes List of all unique permission codes.
 * @property-read string|null $avatar Full URL to the avatar.
 */
final class AdminUser extends Authenticatable implements JWTSubject
{
    use HasFactory;

    /**
     * The roles that belong to the user.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'ipa_user_role', 'user_id', 'role_id')->withTimestamps();
    }

    /**
     * The unit that the user belongs to.
     */
    public function unit()
    {
        return $this->belongsTo(OrgUnit::class, 'primary_unit_id');
    }

    /**
     * Get the primary role code of the user.
     *
     * @return string|null
     */
    public function getRoleAttribute()
    {
        return $this->roles()->first()?->code;
    }

    /**
     * The attributes that should be appened to the model's array form.
     *
     * @var array
     */
    // Note: role_codes and permission_codes are NOT auto-appended to avoid N+1 queries in list views.
    // Use ->load(['roles', 'roles.permissions']) explicitly when these are needed (e.g., /me endpoint).
    protected $appends = [];

    /**
     * Get the roles code list.
     *
     * @return array
     */
    public function getRoleCodesAttribute(): array
    {
        // Use already-loaded relation to avoid N+1 query
        if ($this->relationLoaded('roles')) {
            return $this->roles->pluck('code')->toArray();
        }
        return $this->roles()->pluck('code')->toArray();
    }

    /**
     * Get all permissions code list from all roles.
     *
     * @return array
     */
    public function getPermissionCodesAttribute(): array
    {
        $permissions = collect();
        // Use already-loaded relation to avoid N+1 query
        $roles = $this->relationLoaded('roles') ? $this->roles : $this->roles()->get();
        foreach ($roles as $role) {
            $roleCodes = $role->relationLoaded('permissions')
                ? $role->permissions->pluck('code')
                : $role->permissions()->pluck('code');
            $permissions = $permissions->merge($roleCodes);
        }
        return $permissions->unique()->values()->toArray();
    }

    /**
     * Get the full URL for the user's avatar.
     *
     * @return string|null
     */
    public function getAvatarUrlAttribute()
    {
        $value = $this->attributes['avatar_url'] ?? null;

        if (!$value) {
            return "https://ui-avatars.com/api/?name=" . urlencode($this->full_name ?? 'User') . "&background=DBEAFE&color=3B82F6&bold=true";
        }

        if (str_starts_with($value, 'http')) {
            return $value;
        }

        if (str_starts_with($value, 'avatars/')) {
            return Cloudinary::getUrl($value);
        }

        return rtrim((string) config('app.url'), '/') . '/storage/' . $value;
    }

    /**
     * Check if user has a specific role.
     *
     * @param string|array $roles
     * @return bool
     */
    public function hasRole(string|array $roles): bool
    {
        $roles = is_array($roles) ? $roles : [$roles];
        $roles = array_map('strtoupper', $roles);

        // Use already-loaded relation to avoid N+1 query
        if ($this->relationLoaded('roles')) {
            return $this->roles
                ->contains(fn ($role) => in_array(strtoupper((string) $role->code), $roles));
        }

        return $this->roles()
            ->whereIn(\DB::raw('UPPER(code)'), $roles)
            ->exists();
    }

    /**
     * Check if user has a specific permission.
     *
     * @param string $permissionCode
     * @return bool
     */
    public function hasPermission(string $permissionCode): bool
    {
        // Use already-loaded relation to avoid N+1 query
        if ($this->relationLoaded('roles')) {
            foreach ($this->roles as $role) {
                $perms = $role->relationLoaded('permissions')
                    ? $role->permissions
                    : $role->permissions()->get();
                if ($perms->contains('code', $permissionCode)) {
                    return true;
                }
            }
            return false;
        }

        foreach ($this->roles()->get() as $role) {
            if ($role->permissions()->where('code', $permissionCode)->exists()) {
                return true;
            }
        }
        return false;
    }

    protected $table = 'ipa_user';

    protected $guarded = [];

    protected $casts = [
        'status' => 'integer',
        'primary_unit_id' => 'integer',
        'last_login_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}

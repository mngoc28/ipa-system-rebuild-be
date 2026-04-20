<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

final class AdminUser extends Authenticatable implements JWTSubject
{
    use HasFactory;

    /**
     * The roles that belong to the user.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'ipa_user_role', 'user_id', 'role_id');
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
    protected $appends = ['role_codes', 'permission_codes', 'avatar'];

    /**
     * Get the roles code list.
     *
     * @return array
     */
    public function getRoleCodesAttribute(): array
    {
        return $this->roles()->get()->pluck('code')->toArray();
    }

    /**
     * Get all permissions code list from all roles.
     *
     * @return array
     */
    public function getPermissionCodesAttribute(): array
    {
        $permissions = collect();
        foreach ($this->roles()->get() as $role) {
            $permissions = $permissions->merge($role->permissions()->pluck('code'));
        }
        return $permissions->unique()->values()->toArray();
    }

    /**
     * Get the full URL for the user's avatar.
     *
     * @return string|null
     */
    public function getAvatarAttribute()
    {
        if (!$this->avatar_url) {
            return null;
        }

        if (str_starts_with($this->avatar_url, 'http')) {
            return $this->avatar_url;
        }

        return rtrim((string) config('app.url'), '/') . '/storage/' . $this->avatar_url;
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

        return $this->roles()
            ->whereIn(\DB::raw('UPPER(code)'), $roles)
            ->count() > 0;
    }

    /**
     * Check if user has a specific permission.
     *
     * @param string $permissionCode
     * @return bool
     */
    public function hasPermission(string $permissionCode): bool
    {
        foreach ($this->roles()->get() as $role) {
            if ($role->permissions()->where('code', $permissionCode)->count() > 0) {
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

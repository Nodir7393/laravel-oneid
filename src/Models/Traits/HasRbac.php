<?php

namespace Nodir\OneId\Models\Traits;

use Nodir\OneId\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasRbac
{
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user')
                    ->withPivot('assigned_by')
                    ->withTimestamps();
    }

    public function hasRole(string $slug): bool
    {
        return $this->roles->contains('slug', $slug);
    }

    public function hasAnyRole(array $slugs): bool
    {
        return $this->roles->whereIn('slug', $slugs)->isNotEmpty();
    }

    public function hasPermission(string $slug): bool
    {
        return $this->roles->flatMap->permissions->contains('slug', $slug);
    }

    public function hasAnyPermission(array $slugs): bool
    {
        return $this->roles->flatMap->permissions->whereIn('slug', $slugs)->isNotEmpty();
    }

    public function allPermissions(): \Illuminate\Support\Collection
    {
        return $this->roles->flatMap->permissions->unique('id');
    }

    public function assignRole(string $slug, ?string $assignedBy = null): void
    {
        $role = Role::where('slug', $slug)->firstOrFail();
        if (!$this->hasRole($slug)) {
            $this->roles()->attach($role->id, ['assigned_by' => $assignedBy]);
        }
    }

    public function removeRole(string $slug): void
    {
        $role = Role::where('slug', $slug)->firstOrFail();
        $this->roles()->detach($role->id);
    }

    public function scopeWithRole($q, string $slug)
    {
        return $q->whereHas('roles', fn($q2) => $q2->where('slug', $slug));
    }
}

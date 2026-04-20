<?php

namespace Nodir\OneId\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasUuids;

    protected $fillable = ['slug', 'name', 'description'];

    public function users(): BelongsToMany
    {
        $userModel = config('oneid.user_model', User::class);
        return $this->belongsToMany($userModel, 'role_user')->withPivot('assigned_by')->withTimestamps();
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_role');
    }

    public function hasPermission(string $slug): bool
    {
        return $this->permissions->contains('slug', $slug);
    }
}

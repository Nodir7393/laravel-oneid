<?php

namespace Nodir\OneId\Models;

use Nodir\OneId\Enums\UserStatus;
use Nodir\OneId\Models\Traits\HasRbac;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasUuids, SoftDeletes, HasRbac;

    protected $fillable = [
        'pinfl', 'passport', 'egov_login',
        'first_name', 'middle_name', 'last_name', 'full_name',
        'phone', 'email', 'status', 'password',
        'org_name', 'org_stir', 'position', 'region', 'district',
        'last_login_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'status'        => UserStatus::class,
            'password'      => 'hashed',
            'last_login_at' => 'datetime',
        ];
    }

    /* ---- Status helpers ---- */

    public function isActive(): bool  { return $this->status === UserStatus::ACTIVE; }
    public function isPending(): bool { return $this->status === UserStatus::PENDING; }
    public function isBlocked(): bool { return $this->status === UserStatus::BLOCKED; }

    public function activate(): void { $this->update(['status' => UserStatus::ACTIVE]); }
    public function block(): void    { $this->update(['status' => UserStatus::BLOCKED]); }

    /* ---- Scopes ---- */

    public function scopeActive($q)  { return $q->where('status', UserStatus::ACTIVE); }
    public function scopePending($q) { return $q->where('status', UserStatus::PENDING); }
}

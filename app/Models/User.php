<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRoleEnum;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

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
            'role' => UserRoleEnum::class,
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /**
     * Determine if the user is an admin (SUPER_ADMIN or ADMIN).
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, [UserRoleEnum::SUPER_ADMIN, UserRoleEnum::ADMIN]);
    }

    /**
     * Determine if the user is a super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === UserRoleEnum::SUPER_ADMIN;
    }

    /**
     * Get all admin users (super_admin and admin roles).
     *
     * @return Collection<int, User>
     */
    public static function getAdmins(): Collection
    {
        return static::whereIn('role', [UserRoleEnum::SUPER_ADMIN, UserRoleEnum::ADMIN])->get();
    }
}

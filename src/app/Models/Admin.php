<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use RuntimeException;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token', 'two_factor_code'])]
class Admin extends Authenticatable
{
    public const ROLE_SUPER_ADMIN = 'super_admin';

    public const ROLE_SHOP_ADMIN = 'shop_admin';

    protected static function booted(): void
    {
        static::saving(function (Admin $admin) {
            $isSuperAdmin = $admin->role === self::ROLE_SUPER_ADMIN;

            if ($isSuperAdmin !== is_null($admin->shop_id)) {
                throw new RuntimeException('A super_admin must have no shop_id, and a shop_admin must have a shop_id.');
            }
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'two_factor_expires_at' => 'datetime',
        ];
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function canAccessShop(Shop $shop): bool
    {
        return $this->isSuperAdmin() || $this->shop_id === $shop->id;
    }
}

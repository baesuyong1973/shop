<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'address', 'phone', 'logo_path', 'is_active'])]
class Shop extends Model
{
    protected $appends = ['available_locales'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function admins(): HasMany
    {
        return $this->hasMany(Admin::class);
    }

    public function locales(): HasMany
    {
        return $this->hasMany(ShopLocale::class);
    }

    protected function availableLocales(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->locales->pluck('locale')->values()->all(),
        );
    }
}

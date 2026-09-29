<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A sales unit (個, 箱, kg, ...). Units without a shop are shared by every
 * shop; units with a shop are that shop's own additions.
 */
#[Fillable(['name'])]
class Unit extends Model
{
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Shared units plus the given shop's own units.
     */
    public function scopeAvailableTo(Builder $query, Shop $shop): Builder
    {
        return $query->where(fn (Builder $q) => $q->whereNull('shop_id')->orWhere('shop_id', $shop->id));
    }

    /**
     * Shared units first, then the shop's own, each in creation order.
     */
    public function scopeDisplayOrder(Builder $query): Builder
    {
        return $query->orderByRaw('shop_id IS NULL DESC')->orderBy('id');
    }
}

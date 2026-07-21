<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['shop_id', 'user_id', 'total_amount', 'status'])]
class Order extends Model
{
    protected $appends = ['status_label', 'available_transitions'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_amount' => 'integer',
        ];
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * The order's current status, as a master-data record.
     */
    public function orderStatus(): BelongsTo
    {
        return $this->belongsTo(OrderStatus::class, 'status', 'key');
    }

    protected function statusLabel(): Attribute
    {
        return Attribute::get(fn () => OrderStatus::labelMap()[$this->status] ?? $this->status);
    }

    protected function availableTransitions(): Attribute
    {
        return Attribute::get(fn () => OrderStatus::transitionsFor($this->status));
    }
}

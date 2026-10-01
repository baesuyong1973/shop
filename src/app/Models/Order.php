<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['shop_id', 'user_id', 'total_amount', 'status', 'deposit_amount', 'payment_status'])]
class Order extends Model
{
    /** Placed before deposits existed, or with no payment driver configured. */
    public const PAYMENT_NOT_REQUIRED = 'not_required';

    /** Waiting for the customer to pay the deposit. */
    public const PAYMENT_PENDING = 'pending';

    public const PAYMENT_PAID = 'paid';

    /** Deposit was paid and then refunded (the order was cancelled). */
    public const PAYMENT_REFUNDED = 'refunded';

    /** Deposit was never paid; the order was cancelled and stock restored. */
    public const PAYMENT_EXPIRED = 'expired';

    protected $appends = ['status_label', 'available_transitions', 'remaining_amount'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_amount' => 'integer',
            'deposit_amount' => 'integer',
            'paid_at' => 'datetime',
            'refunded_at' => 'datetime',
        ];
    }

    /**
     * The deposit for an order total: the configured percentage, rounded up
     * to the yen so the deposit never falls short of that percentage.
     */
    public static function depositFor(int $total): int
    {
        $rate = config('payment.deposit_rate');

        return intdiv($total * $rate + 99, 100);
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

    /**
     * Orders the shop should act on: excludes ones still awaiting (or that
     * never received) their deposit.
     */
    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->whereNotIn('payment_status', [self::PAYMENT_PENDING, self::PAYMENT_EXPIRED]);
    }

    protected function statusLabel(): Attribute
    {
        return Attribute::get(fn () => OrderStatus::labelMap()[$this->status] ?? $this->status);
    }

    protected function availableTransitions(): Attribute
    {
        return Attribute::get(fn () => $this->payment_status === self::PAYMENT_PENDING
            ? []
            : OrderStatus::transitionsFor($this->status));
    }

    /**
     * What the customer still pays at pickup.
     */
    protected function remainingAmount(): Attribute
    {
        return Attribute::get(fn () => $this->total_amount - $this->deposit_amount);
    }
}

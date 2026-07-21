<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['key', 'label', 'sort_order', 'is_initial', 'is_void'])]
class OrderStatus extends Model
{
    private static ?array $labelMapCache = null;

    private static ?array $transitionsMapCache = null;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_initial' => 'boolean',
            'is_void' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => self::clearCaches());
        static::deleted(fn () => self::clearCaches());
    }

    private static function clearCaches(): void
    {
        self::$labelMapCache = null;
        self::$transitionsMapCache = null;
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Statuses this one may transition to.
     */
    public function nextStatuses(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'order_status_transitions', 'from_status_id', 'to_status_id')
            ->withTimestamps()
            ->ordered();
    }

    /**
     * The key of the status new orders should start in.
     */
    public static function initialKey(): ?string
    {
        return static::where('is_initial', true)->value('key');
    }

    /**
     * Map of status key => display label, memoized for the duration of the request.
     *
     * @return array<string, string>
     */
    public static function labelMap(): array
    {
        return self::$labelMapCache ??= static::pluck('label', 'key')->all();
    }

    /**
     * Keys of statuses that void an order (restore stock, hidden from prep lists).
     *
     * @return array<int, string>
     */
    public static function voidKeys(): array
    {
        return static::where('is_void', true)->pluck('key')->all();
    }

    /**
     * Map of status key => list of {key,label,is_void} reachable next statuses,
     * memoized for the duration of the request.
     *
     * @return array<string, array<int, array{key: string, label: string, is_void: bool}>>
     */
    public static function transitionsMap(): array
    {
        if (self::$transitionsMapCache !== null) {
            return self::$transitionsMapCache;
        }

        return self::$transitionsMapCache = static::with('nextStatuses:id,key,label,is_void')
            ->get()
            ->mapWithKeys(fn (self $status) => [
                $status->key => $status->nextStatuses
                    ->map(fn (self $next) => ['key' => $next->key, 'label' => $next->label, 'is_void' => $next->is_void])
                    ->values()
                    ->all(),
            ])
            ->all();
    }

    /**
     * @return array<int, array{key: string, label: string, is_void: bool}>
     */
    public static function transitionsFor(?string $key): array
    {
        return static::transitionsMap()[$key] ?? [];
    }
}

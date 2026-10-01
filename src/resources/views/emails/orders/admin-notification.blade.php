<x-mail::message>
# 新規注文が入りました

**注文番号：** {{ $order->id }}

**注文者：** {{ $order->user->name }}（{{ $order->user->email }}）

**店舗：** {{ $order->shop->name }}

<x-mail::table>
| 商品名 | 単価 | 数量 | 小計 |
| :----- | ---: | ---: | ---: |
@foreach ($order->items as $item)
| {{ $item->product_name }} | ¥{{ number_format($item->unit_price) }} | {{ $item->quantity }} | ¥{{ number_format($item->subtotal) }} |
@endforeach
</x-mail::table>

**合計金額：** ¥{{ number_format($order->total_amount) }}
@if ($order->deposit_amount > 0)

**前払い（PayPay・支払い済み）：** ¥{{ number_format($order->deposit_amount) }}

**店頭で受け取る金額：** ¥{{ number_format($order->remaining_amount) }}
@endif

<x-mail::button :url="route('admin.shop.orders.show', ['shop' => $order->shop, 'order' => $order])">
管理画面で確認する
</x-mail::button>

</x-mail::message>

<x-mail::message>
# ご注文ありがとうございます

{{ $order->user->name }} 様

以下の内容でご注文を承りました。

**注文番号：** {{ $order->id }}

**ご注文店舗：** {{ $order->shop->name }}

<x-mail::table>
| 商品名 | 単価 | 数量 | 小計 |
| :----- | ---: | ---: | ---: |
@foreach ($order->items as $item)
| {{ $item->product_name }} | ¥{{ number_format($item->unit_price) }} | {{ $item->quantity }} | ¥{{ number_format($item->subtotal) }} |
@endforeach
</x-mail::table>

**合計金額：** ¥{{ number_format($order->total_amount) }}

</x-mail::message>

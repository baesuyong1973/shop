<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>PayPay（開発用の疑似決済）</title>
    <style>
        body { margin: 0; font-family: sans-serif; background: #f3f4f6; color: #111827; }
        main { max-width: 420px; margin: 48px auto; padding: 24px; background: #fff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 999px; background: #fef3c7; color: #92400e; font-size: 12px; }
        h1 { font-size: 20px; margin: 12px 0; }
        .amount { font-size: 32px; font-weight: bold; margin: 16px 0; }
        p { color: #4b5563; font-size: 14px; }
        form { margin-top: 12px; }
        button { width: 100%; padding: 12px; border: 0; border-radius: 8px; font-size: 16px; cursor: pointer; }
        .pay { background: #ff0033; color: #fff; }
        .cancel { background: #e5e7eb; color: #111827; }
    </style>
</head>
<body>
<main>
    <span class="badge">開発用の疑似決済（実際の支払いは発生しません）</span>
    <h1>PayPayでのお支払い</h1>
    <p>注文番号 {{ $payment['order_id'] }} の前払い</p>
    <div class="amount">¥{{ number_format($payment['amount']) }}</div>

    @if ($payment['status'] === \App\Payments\FakeDepositGateway::STATUS_CREATED)
        <form method="POST" action="{{ route('fake-paypay.update', $reference) }}">
            @csrf
            <input type="hidden" name="pay" value="1">
            <button type="submit" class="pay">支払う</button>
        </form>
        <form method="POST" action="{{ route('fake-paypay.update', $reference) }}">
            @csrf
            <input type="hidden" name="pay" value="0">
            <button type="submit" class="cancel">支払わずに戻る</button>
        </form>
    @else
        <p>この支払いは手続き済みです（{{ $payment['status'] }}）。</p>
    @endif
</main>
</body>
</html>

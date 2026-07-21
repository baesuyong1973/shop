<x-mail::message>
# 管理画面ログインの確認コード

以下の確認コードを管理画面のログイン画面に入力してください。

<x-mail::panel>
<div style="font-size: 28px; letter-spacing: 8px; text-align: center; font-weight: bold;">
{{ $code }}
</div>
</x-mail::panel>

このコードの有効期限は発行から10分間です。

心当たりがない場合は、このメールを無視してください。

{{ config('app.name') }}
</x-mail::message>

# E2Eテスト（Playwright）

実際のブラウザ（Chromium）で、主要な操作の流れを確認するテストです。

## 前提

- 開発環境が起動していること（`docker compose up -d`）。特に `db`・`vite`・`mailpit` を使います。
- 初回のみ、このフォルダで `npm install` と `npx playwright install chromium` を実行します。

## 実行

```
cd e2e
npm test            # すべて実行（画面は表示しない）
npm run test:ui     # 画面付きのUIモードで、1ステップずつ確認しながら実行
npm run test:headed # ブラウザを表示して実行
npm run report      # 前回の結果をHTMLで表示（失敗時の録画・スクリーンショット付き）
```

## 仕組み

- テスト用のアプリサーバー `e2e`（http://localhost:8090）を自動で起動します（`docker-compose.yml` の `e2e` サービス）。
- データベースは開発用とは別の `laravel_e2e` です。実行のたびに `migrate:fresh --seed` で初期状態に戻すので、開発中のデータには影響しません。
- 管理画面の2段階認証コードは Mailpit（http://localhost:8025）に届いたメールから読み取ります。
- 画像ファイルの保存先（`src/storage`）は開発環境と共用です。テストで登録した商品画像は、次回の実行前に自動で削除します。

## テストの内容

| ファイル | 内容 |
|---|---|
| `01-shopping.spec.ts` | カートに入れる → ログイン → 注文 → 在庫の減少・注文完了メッセージ、カートからの削除、商品検索 |
| `02-admin-orders.spec.ts` | 2段階認証（誤ったコード）、店舗管理者による注文キャンセルと在庫の戻り |
| `03-admin-products.spec.ts` | 画像付きの商品登録 → お客様側の店舗ページに画像付きで表示 |

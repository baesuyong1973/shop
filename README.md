# Laravel Docker環境

構成: Nginx + PHP-FPM(8.3) + MySQL 8.0 + Redis

マルチ店舗対応のEC注文管理アプリ（Laravel + Inertia.js + React）。

## ディレクトリ構成

```
.
├── docker-compose.yml
├── docker/
│   ├── php/        (Dockerfile, php.ini)
│   ├── nginx/       (default.conf)
│   └── mysql/       (my.cnf)
└── src/            (Laravelアプリ本体)
```

## GitHubからクローンした場合のセットアップ

`src` にはアプリ本体一式が入っています（`vendor/`, `node_modules/`, `.env` はコミットされていないため各自生成します）。

### 1. リポジトリを取得してイメージをビルド

```bash
git clone <このリポジトリのURL> myapp
cd myapp
docker compose build
```

### 2. コンテナ起動

```bash
docker compose up -d
```

### 3. .env作成

`src/.env.example` は最初からDocker内のサービス名（`db`, `redis`, `mailpit`）に合わせてあるので、コピーするだけで動作します。

```bash
cp src/.env.example src/.env
```

### 4. 依存パッケージのインストール

```bash
# PHP依存 (コンテナ経由)
docker compose exec app composer install

# フロント依存・ビルド (ホスト側にNode.jsが必要)
cd src
npm install
npm run build
cd ..
```

### 5. アプリケーションキー生成・マイグレーション・シード

```bash
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan storage:link
```

`--seed` により以下が自動で作成されます。

- デフォルト店舗（`shops`テーブル、マイグレーション内で自動生成）
- 都道府県・単位マスタ（`PrefectureSeeder` / `UnitSeeder`）
- テスト会員: `test@example.com`（`DatabaseSeeder`）
- 初期スーパー管理者: `admin@example.com` / パスワード `password`（`AdminSeeder`）

**管理画面は2段階認証があります。** ログイン後に届く確認コードは、開発環境では実メールの代わりに Mailpit（http://localhost:8025）で確認してください。

初回ログイン後は、`admin@example.com` のパスワードを変更するか、スーパー管理者画面から新しい管理者を作成し、テスト用アカウントは無効化/削除することを推奨します。

### 6. パーミッション調整（必要な場合）

```bash
docker compose exec app chmod -R 775 storage bootstrap/cache
```

## アクセス

- アプリ: http://localhost:8080
- 管理画面: http://localhost:8080/admin
- Mailpit（開発用メール確認 / 2段階認証コード）: http://localhost:8025
- phpMyAdmin: http://localhost:8081
- MySQL: localhost:3306 (user: laravel / pass: secret / root pass: root)
- Redis: localhost:6379

## よく使うコマンド

```bash
docker compose ps                 # 状態確認
docker compose logs -f app        # ログ確認
docker compose exec app bash      # PHPコンテナに入る
docker compose down               # 停止
docker compose down -v            # 停止 + DBボリューム削除
```

## GitHubへの公開手順（初回のみ）

このディレクトリはまだGitリポジトリ化されていません。初めてGitHubに上げる場合の手順です。

```bash
git init
git add .
git status                 # vendor/node_modules/.envが含まれていないか必ず確認
git commit -m "Initial commit"

# GitHub側で先に空のリポジトリを作成してから
git remote add origin <作成したリポジトリのURL>
git branch -M main
git push -u origin main
```

`.gitignore` により `src/vendor`, `src/node_modules`, `src/.env` は既に除外されています。`.env`に本番相当の秘密情報が入る場合は、コミット前に内容を必ず確認してください。

## ゼロから新規プロジェクトを作り直す場合（参考）

`src` を空にした状態から再構成したい場合のみ、以下の手順を使います（通常は不要）。

```bash
docker compose run --rm app composer create-project laravel/laravel .
```

その後は上記「GitHubからクローンした場合のセットアップ」の手順4以降と同様です。

# AWS(EC2)へのデプロイ手順

`docker-compose.yml`（ローカル開発用）はそのまま。ここではEC2上で`docker-compose.prod.yml`を動かし、インターネット経由で確認できるようにする。

## 前提

- ローカルにAWS CLI v2 / Terraformがインストール済みで、`aws configure`で認証情報が設定済みであること
- ローカルにSSH鍵ペアがあること（なければ `ssh-keygen -t ed25519 -f ~/.ssh/id_ed25519`）
- Gmailなど、実際にメールを送れるSMTPアカウントと（Gmailの場合）アプリパスワードを用意していること

## 1. Terraformでインフラ構築

```bash
cd infra/terraform
cp terraform.tfvars.example terraform.tfvars
# terraform.tfvars を編集: my_ip_cidr（自分のグローバルIP/32）, ssh_public_key_path

terraform init
terraform plan
terraform apply   # 課金発生（目安 月$15〜20）。内容を確認してから実行
```

`terraform apply`後に表示される`public_ip` / `ssh_command`を控える。

## 2. EC2へ接続してアプリを配置

```bash
ssh -i <秘密鍵のパス> ec2-user@<public_ip>

git clone https://github.com/baesuyong1973/shop.git
cd shop
```

## 3. 環境変数ファイルを作成

`.env`（リポジトリ直下、`docker-compose.prod.yml`用）:

```bash
cp .env.prod.example .env
# DB_PASSWORD, DB_ROOT_PASSWORD を強力なパスワードに変更
```

`src/.env`（Laravel本体用）:

```bash
cp src/.env.example src/.env
```

`src/.env`を編集し、以下を本番向けに変更する:

```
APP_ENV=production
APP_DEBUG=false
APP_URL=http://<public_ip>

DB_PASSWORD=<.envのDB_PASSWORDと同じ値>

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=<Gmailアドレス>
MAIL_PASSWORD=<Gmailアプリパスワード>
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=<Gmailアドレス>
```

## 4. フロント資産のビルド（Node.jsをホストに入れずコンテナで実行）

```bash
docker run --rm -v "$PWD/src:/app" -w /app node:20-alpine sh -c "npm ci && npm run build"
```

## 5. コンテナ起動・初期化

```bash
docker compose -f docker-compose.prod.yml up -d --build

docker compose -f docker-compose.prod.yml exec app composer install --no-dev --optimize-autoloader
docker compose -f docker-compose.prod.yml exec app php artisan key:generate
docker compose -f docker-compose.prod.yml exec app php artisan migrate --seed --force
docker compose -f docker-compose.prod.yml exec app php artisan storage:link
docker compose -f docker-compose.prod.yml exec app chmod -R 775 storage bootstrap/cache
```

## 6. 動作確認

ブラウザで `http://<public_ip>/` にアクセスし、以下を確認する。

- トップページが表示される
- `/admin` からログインでき、2段階認証コードがGmail宛に届く
- 管理画面ダッシュボードが表示される
- 商品画像アップロードが保存・表示される

## よく使うコマンド（EC2上）

```bash
docker compose -f docker-compose.prod.yml ps
docker compose -f docker-compose.prod.yml logs -f app
docker compose -f docker-compose.prod.yml down
```

## リソースの削除（課金を止めたい場合）

```bash
cd infra/terraform
terraform destroy
```

## スコープ外

- HTTPS化・独自ドメイン
- 複数インスタンス化・オートスケーリング（その場合は画像保存先をS3に移行する必要あり）
- RDS/ElastiCacheへの移行、CI/CD

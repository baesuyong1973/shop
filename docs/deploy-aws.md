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
ssh -i ~/.ssh/id_ed25519 ec2-user@35.76.62.180

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
docker compose -f docker-compose.prod.yml exec app chown -R :www-data storage bootstrap/cache
docker compose -f docker-compose.prod.yml exec app chmod -R 775 storage bootstrap/cache
```

`storage`/`bootstrap/cache`はホスト側のbind mountなので、所有者はEC2ユーザー（uid/gid 1000）のままになる。コンテナ内のphp-fpmは`www-data`で動いているため、`chown`でグループを`www-data`に合わせておかないと書き込み権限がなく500エラーになる。

## 6. 動作確認

ブラウザで `http://<public_ip>/` にアクセスし、以下を確認する。

- トップページが表示される
- `/admin` からログインでき、2段階認証コードがGmail宛に届く
- 管理画面ダッシュボードが表示される
- 商品画像アップロードが保存・表示される

## 7. ローカル修正を本番に反映する手順

EC2側は`./src`をコンテナへバインドマウントしているだけで、コードをイメージに焼き込んでいない。そのため基本は「git pullして、必要な部分だけ入れ直す」形になる。

```bash
# ローカルで変更をコミット・push
git add .
git commit -m "..."
git push origin main
```

```bash
# EC2へSSH接続し、最新コードを取得
ssh -i ~/.ssh/id_ed25519 ec2-user@<public_ip>
cd shop
git pull origin main
```

ここから先は変更内容に応じて必要なものだけ実行する。

```bash
# composer.lock が変わった場合
docker compose -f docker-compose.prod.yml exec app composer install --no-dev --optimize-autoloader

# フロント資産（JS/CSS）を変更した場合
docker run --rm -v "$PWD/src:/app" -w /app node:20-alpine sh -c "npm ci && npm run build"

# マイグレーションを追加した場合
docker compose -f docker-compose.prod.yml exec app php artisan migrate --force

# .env や設定キャッシュ関連を変更した場合
docker compose -f docker-compose.prod.yml exec app php artisan config:cache
docker compose -f docker-compose.prod.yml exec app php artisan view:clear
docker compose -f docker-compose.prod.yml exec app php artisan route:clear

# Dockerfile や docker-compose.prod.yml 自体を変更した場合のみ再ビルド
docker compose -f docker-compose.prod.yml up -d --build
```

PHPコードだけの変更（Bladeやコントローラなど）は`git pull`だけで反映される（bind mount + php-fpmなのでコンテナ再起動不要）。ただしopcacheが有効な場合はキャッシュが残ることがあるので、反映されない時は`docker compose -f docker-compose.prod.yml restart app`を実行する。

## よく使うコマンド（EC2上）

```bash
docker compose -f docker-compose.prod.yml ps
docker compose -f docker-compose.prod.yml logs -f app
docker compose -f docker-compose.prod.yml down
```

## 8. HTTPS化（独自ドメイン + Let's Encrypt）

証明書はIPアドレスに対しては発行できないため、まず独自ドメインが必要。取得済みのドメインがある前提で進める。

### 8-1. DNS設定

ドメインのレジストラ（お名前.com、Route53など）のDNS管理画面で、Aレコードを`terraform apply`後の`public_ip`（Elastic IP）に向ける。

```
A  example.com      -> <public_ip>
A  www.example.com  -> <public_ip>   # 必要なら
```

反映には数分〜数時間かかることがある。`dig example.com`や`nslookup example.com`で名前解決できることを確認してから次に進む。

### 8-2. セキュリティグループの更新（443番を開放）

`infra/terraform/main.tf`に443番ポートのingressを追加済み。EC2へ反映する。

```bash
cd infra/terraform
terraform plan
terraform apply
```

### 8-3. 最新コードの取得・nginx再起動

`docker-compose.prod.yml`のnginxサービスに証明書用ボリューム（`./docker/certbot/conf`, `./docker/certbot/www`）と443番ポート、`docker/nginx/default.conf`にACMEチャレンジ用locationを追加済み。EC2側でpullして反映する。

```bash
ssh -i ~/.ssh/id_ed25519 ec2-user@<public_ip>
cd shop
git pull origin main
docker compose -f docker-compose.prod.yml up -d
```

### 8-4. 証明書の取得（certbot、webroot方式）

nginxを止めずに取得できる。`<DOMAIN>`と`<EMAIL>`は自分のものに置き換える。

```bash
docker run --rm \
  -v "$PWD/docker/certbot/conf:/etc/letsencrypt" \
  -v "$PWD/docker/certbot/www:/var/www/certbot" \
  certbot/certbot certonly --webroot -w /var/www/certbot \
  -d <DOMAIN> \
  --email <EMAIL> --agree-tos --no-eff-email
```

成功すると`docker/certbot/conf/live/<DOMAIN>/`に`fullchain.pem`・`privkey.pem`が生成される。

### 8-5. nginx設定をHTTPS版に切り替え

`docker/nginx/default.conf.https-example`をコピーして`<DOMAIN>`を置換し、`default.conf`を上書きする。

```bash
sed "s/<DOMAIN>/example.com/g" docker/nginx/default.conf.https-example > docker/nginx/default.conf
docker compose -f docker-compose.prod.yml restart web
```

ブラウザで`https://<DOMAIN>/`にアクセスして表示を確認する。`http://<DOMAIN>/`は自動的に`https://`へリダイレクトされる。

### 8-6. アプリ側の設定変更

`src/.env`を編集し、config系のキャッシュを作り直す。

```
APP_URL=https://<DOMAIN>
```

```bash
docker compose -f docker-compose.prod.yml exec app php artisan config:cache
```

### 8-7. 証明書の自動更新

Let's Encryptの証明書は90日で失効する。EC2上のcrontab（`crontab -e`）に以下を登録し、更新後にnginxへ反映させる。

```cron
0 3 * * * cd /home/ec2-user/shop && docker run --rm -v "$PWD/docker/certbot/conf:/etc/letsencrypt" -v "$PWD/docker/certbot/www:/var/www/certbot" certbot/certbot renew --quiet && docker compose -f docker-compose.prod.yml restart web
```

## リソースの削除（課金を止めたい場合）

```bash
cd infra/terraform
terraform destroy
```

## スコープ外

- 複数インスタンス化・オートスケーリング（その場合は画像保存先をS3に移行する必要あり）
- RDS/ElastiCacheへの移行、CI/CD

.PHONY: up down restart build bash migrate fresh seed install key logs ps scrape-races

# Dockerコンテナを起動（バックグラウンド）
up:
	docker-compose up -d

# Dockerコンテナを停止
down:
	docker-compose down

# Dockerコンテナを再起動
restart:
	docker-compose restart

# Dockerイメージをビルド
build:
	docker-compose build --no-cache

# Dockerコンテナを再ビルドして起動
rebuild:
	docker-compose down && docker-compose build --no-cache && docker-compose up -d

# appコンテナにbashで入る
bash:
	docker-compose exec app bash

# マイグレーション実行
migrate:
	docker-compose exec app php artisan migrate

# マイグレーションをリセットしてシードを実行
fresh:
	docker-compose exec app php artisan migrate:fresh --seed

# シーダーのみ実行
seed:
	docker-compose exec app php artisan db:seed

# Composerパッケージのインストール
install:
	docker-compose exec app composer install

# アプリケーションキーの生成
key:
	docker-compose exec app php artisan key:generate

# コンテナのログを表示
logs:
	docker-compose logs -f

# appコンテナのログを表示
logs-app:
	docker-compose logs -f app

# webコンテナのログを表示
logs-web:
	docker-compose logs -f web

# DBコンテナのログを表示
logs-db:
	docker-compose logs -f db

# 実行中のコンテナ一覧
ps:
	docker-compose ps

# キャッシュをクリア
cache-clear:
	docker-compose exec app php artisan cache:clear
	docker-compose exec app php artisan config:clear
	docker-compose exec app php artisan route:clear
	docker-compose exec app php artisan view:clear

# レース情報をスクレイピング
scrape-races:
	docker-compose exec app php artisan scrape:races

# Tinkerを起動
tinker:
	docker-compose exec app php artisan tinker

# npm install
npm-install:
	docker-compose exec app npm install

# npm run dev
npm-dev:
	docker-compose exec app npm run dev

# npm run build
npm-build:
	docker-compose exec app npm run build

# ストレージリンクを作成
storage-link:
	docker-compose exec app php artisan storage:link

# 初期セットアップ（初回起動時に使用）
setup: up
	@echo "コンテナの起動を待機中..."
	@sleep 5
	docker-compose exec app composer install
	docker-compose exec app php artisan key:generate
	docker-compose exec app php artisan migrate
	docker-compose exec app php artisan storage:link
	@echo "セットアップ完了！ http://localhost:8080 にアクセスしてください"

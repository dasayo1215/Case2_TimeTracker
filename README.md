README

# アプリケーション名

coachtech 勤怠管理アプリ


## 使用技術（実行環境）

- OS：Windows 11（WSL2 上で動作）
- バックエンドフレームワーク：Laravel 12.32.5
- プログラミング言語：PHP 8.2.29
- コンテナ管理：Docker / docker-compose
- データベース：MySQL 8.0.26
- バージョン管理：Git / GitHub
- メール開発環境：MailHog
- フロントエンド：React 18.x + Vite（完全SPA構成）
- HTTPクライアント：Axios
- ルーティング：React Router DOM
- 認証：Laravel Fortify（メール認証は後で実装予定）

## 環境構築
- DockerDesktopアプリを立ち上げ、下記を実行してください。
```
git clone https://github.com/dasayo1215/Case2_TimeTracker.git
cd Case2_TimeTracker
make setup
```
※もしmakeがない環境なら sh setup.sh で代用可能です。
※MySQLは、OSによって起動しない場合があるのでそれぞれのPCに合わせてdocker-compose.ymlファイルを編集してください。

## データベース初期化とシーディング
初回セットアップ時に `make setup` または `sh setup.sh` を実行すると、自動的にマイグレーションとシーディングが実行されます。
これにより以下のダミーデータが投入されます：

- 管理者ユーザー
  - Email: `admin@example.com`
  - Password: `admin1234`
- 一般ユーザー 5 名
- 各ユーザーに 10 日分の勤怠データ
- 各勤怠に 1〜3 件の休憩データ

開発中にデータベースをリセットしたい場合は以下を実行してください：

```bash
php artisan migrate:fresh --seed
```

## URL
- 開発環境 (React SPA)：http://localhost:5173/
※フロントエンド (Vite Dev Server) のエントリーポイントです。
- API (Laravel)：http://localhost/api/
※バックエンドのAPIエンドポイントです。/api/items などにアクセスできます。
- phpMyAdmin：http://localhost:8080/
※MySQL のデータをブラウザから確認・編集できます。
- MailHog UI：http://localhost:8025/
※ローカル環境で送信された認証メールや通知メールを確認できます。

## PHPUnit テストの実行方法（当プロジェクト用）

## その他

## ER図

## 画面例
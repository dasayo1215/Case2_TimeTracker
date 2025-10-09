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

- 管理者ユーザー
  - Email: `admin@example.com`
  - Password: `admin1234`
- 一般ユーザー数は `.env` の `SEED_USER_COUNT` で設定可能です（デフォルト: 10）
  - 例: `SEED_USER_COUNT=1000` とすると1000人分のデータが生成されます。

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
- 休憩時間は0回でも可とし、出勤から退勤まで休憩を登録しない勤務も可能としました。
- 管理者は、一般ユーザーによる修正申請中（ステータス：pending）の勤怠データであっても修正可能としました。
- 管理者が勤怠データを修正した場合、承認フローを介さず即時に確定（approved）としました。
- 承認済み（approved）の勤怠データは、一般ユーザー・管理者共に再修正可能としました。

## ER図

## 画面例
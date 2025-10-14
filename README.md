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

## PHPUnit テスト（当プロジェクト用）

### テスト範囲について
- 本課題は本来、Laravel（Blade）を用いたサーバーサイドレンダリング構成を前提としており、画面操作を含めた動作確認も PHPUnit の Feature テストで完結させる想定です。
- しかし本プロジェクトでは、フロントエンドを React による SPA（Single Page Application）として実装しているため、UI操作や画面遷移などの動作は PHPUnit のテスト範囲外となります。
- そのため、Laravel 側では API レスポンスおよびデータベース処理を中心に検証し、React 側のボタン操作・ページ遷移・表示更新などの挙動はブラウザ上で別途確認しています。

### テストIDとテストファイル対応表
|ID|項目|	対応テストファイル|
|---|---|---|
|1|	認証機能（一般ユーザー）|	User/Auth/RegisterTest.php|
|2|	ログイン認証機能（一般ユーザー）|	User/Auth/LoginTest.php|
|3|	ログイン認証機能（管理者）|	Admin/Auth/LoginTest.php|
|4|	日時取得機能|	User/Attendance/StatusTest.php|
|5|	ステータス確認機能|	User/Attendance/StatusTest.php|
|6|	出勤機能|	User/Attendance/ClockInTest.php|
|7|	休憩機能|	User/Attendance/BreakTimeTest.php|
|8|	退勤機能|	User/Attendance/ClockOutTest.php|
|9|	勤怠一覧情報取得機能（一般ユーザー）|	User/Attendance/ListTest.php|
|10|	勤怠詳細情報取得機能（一般ユーザー）|	User/Attendance/DetailTest.php|
|11|勤怠詳細情報修正機能（一般ユーザー）|	User/Attendance/CorrectionValidationTest.php, CorrectionRequestTest.php|
|12|	勤怠一覧情報取得機能（管理者）|	Admin/Attendance/ListTest.php|
|13|	勤怠詳細情報取得・修正機能（管理者）|	Admin/Attendance/DetailTest.php|
|14|	ユーザー情報取得機能（管理者）|	Admin/Staff/ListTest.php|
|15|	勤怠情報修正承認機能（管理者）|	Admin/Attendance/ApprovalTest.php|
|16|	メール認証機能（応用）|	User/EmailVerificationTest.php|

### テスト実行手順

#### 1. テスト用データベースを作成
```bash
docker-compose exec mysql bash
mysql -u root -p

#MySQLログイン後
CREATE DATABASE demo_test;
SHOW DATABASES;
```

#### 2. APP_KEY の生成
```
docker-compose exec php bash
php artisan key:generate --env=testing
php artisan config:clear

```

#### 3. テストの実行（すべてのテストを実行）
```
php artisan test --env=testing tests/Feature
```

### 補足事項
- 当プロジェクトでは、テスト環境を .env.testing ファイルにより構築しています。
- 各 Feature テストクラスで use RefreshDatabase; を使用しているため、テストごとに自動でマイグレーションが実行されます。
- Seeder は各テスト内で必要なものだけを呼び出す構成です。
- phpunit.xml は編集・使用しておらず、.env.testing の設定で環境を切り替えています。
- 誤って .env の本番DBを使用しないよう注意してください。

## その他
- 休憩時間は0回でも可とし、出勤から退勤まで休憩を登録しない勤務も可能としました。
- 管理者は、一般ユーザーによる修正申請中（ステータス：pending）の勤怠データであっても修正可能としました。
- 管理者が勤怠データを修正した場合、承認フローを介さず即時に確定（approved）としました。
- 承認済み（approved）の勤怠データは、一般ユーザー・管理者共に再修正可能としました。

## ER図

## 画面例
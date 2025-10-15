README

# アプリケーション名

coachtech 勤怠管理アプリ


## 使用技術（実行環境）

### バックエンド
- フレームワーク：Laravel 12.32.5
- 言語：PHP 8.2.29
- データベース：MySQL 8.0.26
- 認証：Laravel Fortify（Cookieベース認証、CSRF保護にSanctum使用）
- メール開発環境：MailHog
- コンテナ管理：Docker / docker-compose

### フロントエンド
- フレームワーク：React 18.x + Vite（完全SPA構成）
- HTTPクライアント：Axios
- ルーティング：React Router DOM

### 開発環境
- OS：Windows 11（WSL2 上で動作）
- バージョン管理：Git / GitHub

## 環境構築
- DockerDesktopアプリを立ち上げ、下記を実行してください。
```
git clone https://github.com/dasayo1215/Case2_TimeTracker.git
cd Case2_TimeTracker
make setup
```
- ※もしmakeがない環境なら sh setup.sh で代用可能です。
- ※MySQLは、OSによって起動しない場合があるのでそれぞれのPCに合わせてdocker-compose.ymlファイルを編集してください。

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
  - ※フロントエンド (Vite Dev Server) のエントリーポイントです。
- API (Laravel)：http://localhost/api/
  - ※バックエンドのAPIエンドポイントです。/api/items などにアクセスできます。
- phpMyAdmin：http://localhost:8080/
  - ※MySQL のデータをブラウザから確認・編集できます。
- MailHog UI：http://localhost:8025/
  - ※ローカル環境で送信された認証メールや通知メールを確認できます。

## PHPUnit テスト（当プロジェクト用）

本テスト群は、テストケースID（1～16）に対応し、
Laravel側APIの挙動とDB整合性を中心に検証する構成です。

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
|11|勤怠詳細情報修正機能（一般ユーザー）|	User/Attendance/CorrectionValidationTest.php, User/Attendance/CorrectionRequestTest.php|
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

## MailHog の利用について（操作不要）

- メール送信機能の動作確認はMailHogのWeb UI（ http://localhost:8025/ ）で行います。
- Laravelの `.env` にメール送信設定は以下のようにしてあります。

```env
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
```

- これにより、ローカルのメール送信はMailHog経由となり、実際に外部には送信されません。
- メール認証や通知メールを受け取ったかどうかは、MailHogのUIで確認できます。

### 補足事項
- 当プロジェクトでは、テスト環境を .env.testing ファイルにより構築しています。
- 各 Feature テストクラスで use RefreshDatabase; を使用しているため、テストごとに自動でマイグレーションが実行されます。
- Seeder は各テスト内で必要なものだけを呼び出す構成です。
- phpunit.xml は編集・使用しておらず、.env.testing の設定で環境を切り替えています。
- 誤って .env の本番DBを使用しないよう注意してください。

## その他
- 休憩時間は 0 回でも可（休憩を登録せず勤務終了可能）
- 管理者は、一般ユーザーによる修正申請中（pending）の勤怠も修正可能
- 管理者による修正は承認フローを介さず即時確定（approved）
- 承認済み（approved）の勤怠データも、再修正が可能
- シーディングでは現在 10人のユーザー を作成しますが、将来的に1,000人規模のユーザー数を想定し、テストにてそのパフォーマンス確認も実施済みです。シーディングにおけるユーザー数の変更は、.env内の「SEED_USER_COUNT=」で指定可能です（指定しない場合、デフォルトは10人）。

## ER図

## 画面例
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

## データベース初期化とシーディング、ログイン情報
初回セットアップ時に `make setup` または `sh setup.sh` を実行すると、自動的にマイグレーションとシーディングが実行されます。

- 管理者ユーザー
  - Email: `admin@example.com`
  - Password: `admin1234`
- 一般ユーザー
  - Email: `user@example.com`
  - Password: `user1234`
- ランダム一般ユーザー
  - 上記の固定ユーザーに加えて、`.env` の `SEED_USER_COUNT` で指定した数の一般ユーザーをランダムに生成します（デフォルト: 10）。
  - 例: `SEED_USER_COUNT=1000` とすると1000人分のデータが生成されます。

### 開発中のデータベースリセットについて

#### テーブルのみ再作成
アプリケーションのテーブル構造をリセットしたい場合は以下を実行します。
```bash
docker-compose exec php php artisan migrate:fresh --seed
```

#### MySQLデータ削除を伴う完全リセット
MySQLのデータボリュームを含めて完全にリセットしたい場合は以下を実行します。
```bash
docker-compose down -v
docker-compose up -d
docker-compose exec php php artisan migrate --seed
```
※ このプロジェクトの MySQL データは named volume (mysql_data) で管理されているため、
docker-compose down -v で安全にデータを初期化できます。

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
※パスワードは docker-compose.yml 内の MYSQL_ROOT_PASSWORD に記載されています。

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
<img width="701" height="361" alt="case2 drawio" src="https://github.com/user-attachments/assets/56a3827d-1fbd-446f-ba04-ab74518c2e70" />


## 画面例
### 一般ユーザー向け画面
<img width="1577" height="1138" alt="スクリーンショット 2025-10-15 193420" src="https://github.com/user-attachments/assets/88531156-50c0-4652-980b-f541b4306b1a" />
<img width="1578" height="1130" alt="スクリーンショット 2025-10-15 193432" src="https://github.com/user-attachments/assets/29670353-a038-477c-bc18-84851b967959" />
<img width="1577" height="1126" alt="スクリーンショット 2025-10-15 193500" src="https://github.com/user-attachments/assets/61f0d4b6-f4c2-4a32-8105-675d21f1e149" />
<img width="1576" height="1135" alt="スクリーンショット 2025-10-15 193545" src="https://github.com/user-attachments/assets/792cf291-4118-4d02-9a45-46f13a9b4954" />
<img width="1574" height="1133" alt="スクリーンショット 2025-10-15 193554" src="https://github.com/user-attachments/assets/cac9be39-5967-4629-a37b-a39f78c24090" />
<img width="1584" height="1137" alt="スクリーンショット 2025-10-15 193600" src="https://github.com/user-attachments/assets/fa889a5b-3b62-48e7-87ed-12041922cbc5" />
<img width="1577" height="1132" alt="スクリーンショット 2025-10-15 193630" src="https://github.com/user-attachments/assets/8829fb64-60eb-44d9-aaaa-a56b294c5f08" />
<img width="1576" height="1131" alt="スクリーンショット 2025-10-15 193640" src="https://github.com/user-attachments/assets/cfb556d9-8b38-40c3-86e8-c0fcc9438b1b" />
<img width="1579" height="1128" alt="スクリーンショット 2025-10-15 193650" src="https://github.com/user-attachments/assets/97ce50dd-74ae-4bd3-9296-fca77488f1af" />
<img width="1574" height="1134" alt="スクリーンショット 2025-10-15 193720" src="https://github.com/user-attachments/assets/737d20c1-83fd-4c20-8570-58955ca5324c" />
<img width="1577" height="1128" alt="スクリーンショット 2025-10-15 193729" src="https://github.com/user-attachments/assets/7d97fca7-f821-4069-87ce-14303f88a84c" />

### 管理者向け画面
<img width="1572" height="1130" alt="スクリーンショット 2025-10-15 193801" src="https://github.com/user-attachments/assets/67e47cae-f020-43ad-8a14-1ea1dd169eb3" />
<img width="1574" height="1136" alt="スクリーンショット 2025-10-15 193815" src="https://github.com/user-attachments/assets/ab20512e-5b41-42be-a014-7f776d5247e4" />
<img width="1573" height="1124" alt="スクリーンショット 2025-10-15 193830" src="https://github.com/user-attachments/assets/673632b7-d6ea-4d53-933a-9c2f457810d7" />
<img width="1576" height="1137" alt="スクリーンショット 2025-10-15 193849" src="https://github.com/user-attachments/assets/6ef03a10-78aa-4807-89a2-6fc559c8c0e5" />
<img width="1579" height="1130" alt="スクリーンショット 2025-10-15 193858" src="https://github.com/user-attachments/assets/8d1790e5-3f5a-4ede-8cea-d02d214a468f" />
<img width="1573" height="1132" alt="スクリーンショット 2025-10-15 193906" src="https://github.com/user-attachments/assets/573f666c-e7a1-449c-9206-89326a94b415" />
<img width="1572" height="1123" alt="スクリーンショット 2025-10-15 193919" src="https://github.com/user-attachments/assets/15f83435-cbb5-4584-bd11-4bacf2e1b1f5" />
<img width="1575" height="1121" alt="スクリーンショット 2025-10-15 193955" src="https://github.com/user-attachments/assets/d1e3c854-63cb-4905-892b-9c7811c2132a" />
<img width="1573" height="1123" alt="スクリーンショット 2025-10-15 194033" src="https://github.com/user-attachments/assets/d9298ea4-547c-4679-8d08-eddafdc7a2c2" />
<img width="1576" height="1123" alt="スクリーンショット 2025-10-15 194040" src="https://github.com/user-attachments/assets/a15fd243-c458-4089-8a83-92233b8bd752" />

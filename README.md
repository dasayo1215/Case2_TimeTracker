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
以下は **Case2_TimeTracker** のテーブル構成を示したER図です。

```mermaid
erDiagram
    USERS ||--o{ ATTENDANCES : has
    ATTENDANCES ||--o{ BREAKS : has

    USERS {
        bigint id PK
        varchar(255) name
        varchar(255) email UNIQUE
        varchar(255) password
        enum role("admin","user")
        timestamp email_verified_at
        timestamp created_at
        timestamp updated_at
    }

    ATTENDANCES {
        bigint id PK
        bigint user_id FK
        date work_date
        datetime clock_in
        datetime clock_out
        varchar(255) remarks
        enum status("draft","pending","approved")
        datetime submitted_at
        datetime approved_at
        timestamp created_at
        timestamp updated_at
    }

    BREAKS {
        bigint id PK
        bigint attendance_id FK
        datetime break_start
        datetime break_end
        timestamp created_at
        timestamp updated_at
    }
```

## 画面例
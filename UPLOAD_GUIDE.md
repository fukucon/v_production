# KaleidoChrome サーバーアップロードガイド

## 📁 アップロードするファイル

### 必須ファイル
- すべてのHTML, CSS, JS, PHPファイル
- `.htaccess`ファイル
- `includes/`フォルダ全体
- `admin_kc/`フォルダ全体
- `images/`フォルダ全体
- `uploads/`フォルダ（空でもOK）
- `sql/`フォルダ（データベース作成用）

### アップロードしないファイル
- `database/`フォルダ（SQLiteファイル - ローカル開発用）
- `.env`ファイル（サーバーで別途作成）
- `README*.md`ファイル（オプション）
- `.git`フォルダ（Git管理用）

## 🔧 サーバー設定手順

### 1. データベース作成（お名前ドットコムの管理画面）

1. お名前ドットコムのコントロールパネルにログイン
2. 「データベース」メニューを選択
3. 新規データベース作成:
   - データベース名: `iofy8_kaleidochrome`
   - ユーザー名: `iofy8_admin`
   - パスワード: 自分で設定（メモしておく）

### 2. .envファイルの作成

サーバーのルートディレクトリに`.env`ファイルを作成:

```env
DB_HOST=mysql1036.onamae.ne.jp
DB_NAME=iofy8_kaleidochrome
DB_USER=iofy8_admin
DB_PASS=設定したパスワード
SITE_URL=https://kaleidochrome.com
DEBUG_MODE=false
```

**重要**:
- パスワードは必ず実際の値に変更
- `.htaccess`で保護されているため安全

### 3. SQLファイルの実行（phpMyAdmin）

phpMyAdminにアクセスし、以下の順番で実行:

#### 1. setup.sql（基本テーブル）
```bash
sql/setup.sql
```
- admin_usersテーブル
- postsテーブル
- imagesテーブル
- 初期管理者アカウント作成（username: admin, password: admin123）

#### 2. create_talents_table.sql（タレントテーブル）
```bash
sql/create_talents_table.sql
```

#### 3. add_slug_to_talents_mysql.sql（slug追加）
```bash
sql/add_slug_to_talents_mysql.sql
```

#### 4. create_post_talents_table.sql（記事とタレントの関連）
```bash
sql/create_post_talents_table.sql
```

### 4. ファイルパーミッション設定

以下のフォルダに書き込み権限を付与:

```
uploads/ → 755または777
database/ → 削除してOK（本番では不要）
```

### 5. 動作確認

1. **トップページ**: https://kaleidochrome.com/
2. **ブログ**: https://kaleidochrome.com/blog
3. **タレント一覧**: https://kaleidochrome.com/talents
4. **管理画面**: https://kaleidochrome.com/admin_kc/

### 6. 初期設定

1. 管理画面にログイン:
   - ユーザー名: `admin`
   - パスワード: `admin123`

2. **必ずパスワードを変更**してください

3. タレント情報を追加

## 🔒 セキュリティチェックリスト

- [ ] `.env`ファイルのパスワードを設定
- [ ] 管理者パスワードを`admin123`から変更
- [ ] `DEBUG_MODE`が`false`になっているか確認
- [ ] `.htaccess`がアップロードされているか確認
- [ ] `uploads/`フォルダのパーミッションが適切か確認

## 📝 トラブルシューティング

### データベース接続エラー
- `.env`ファイルの内容を確認
- データベース名、ユーザー名、パスワードが正しいか確認
- お名前ドットコムの管理画面でデータベースが作成されているか確認

### 500 Internal Server Error
- `.htaccess`の構文エラーをチェック
- PHPのエラーログを確認
- ファイルパーミッションを確認

### 画像が表示されない
- `images/`フォルダがアップロードされているか確認
- 画像ファイルのパスが正しいか確認

### 管理画面にアクセスできない
- `admin_kc/`フォルダがアップロードされているか確認
- URLが`https://kaleidochrome.com/admin_kc/`になっているか確認

## 📞 サポート

問題が解決しない場合は、以下を確認してください:
- PHPのバージョン: 7.4以上
- MySQLのバージョン: 5.7以上
- mod_rewriteが有効になっているか

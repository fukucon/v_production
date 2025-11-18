# KaleidoChrome - セットアップ手順

## 📋 概要
このドキュメントは、KaleidoChromeサイトをお名前ドットコムにアップロードして動作させるための手順書です。

---

## 🚀 セットアップ手順

### 1. データベースのセットアップ

#### 1-1. phpMyAdminにログイン
- お名前ドットコムのコントロールパネルから「phpMyAdmin」を開く
- データベース `iofy8_kaleidochrome` を選択

#### 1-2. SQLファイルの実行
1. 「インポート」タブをクリック
2. `sql/setup.sql` を選択してアップロード
3. 「実行」ボタンをクリック

これで以下のテーブルが作成されます：
- `admin_users` - 管理者アカウント
- `posts` - ブログ記事
- `images` - 画像ファイル管理

#### 1-3. 初期管理者アカウント
```
ユーザー名: admin
パスワード: admin123
```
**重要: 初回ログイン後、必ずパスワードを変更してください！**

---

### 2. ファイルのアップロード

#### 2-1. FTPでファイルをアップロード
以下のファイル・ディレクトリをすべてアップロード：

```
/home/zono/v_production/
├── admin_kc/          ← 管理画面（重要）
├── includes/          ← 共通PHPファイル
├── sql/               ← SQLスクリプト
├── uploads/           ← 画像保存先（空ディレクトリ）
├── images/            ← 既存画像
├── *.html             ← 既存HTMLファイル
├── *.php              ← PHPファイル
├── styles.css
├── script.js
└── .htaccess
```

#### 2-2. パーミッション設定
FTPクライアントまたはお名前ドットコムのファイルマネージャーで：
- `uploads/` ディレクトリ → **755** または **777**

---

### 3. 動作確認

#### 3-1. サイト表示確認
```
https://kaleidochrome.com/
```
- トップページが正常に表示されるか確認
- ナビゲーションに「ブログ」リンクが追加されているか確認

#### 3-2. ブログページ確認
```
https://kaleidochrome.com/blog.php
```
- サンプル記事「KaleidoChromeへようこそ！」が表示されるか確認

#### 3-3. 管理画面ログイン
```
https://kaleidochrome.com/admin_kc/
```
1. ユーザー名: `admin`
2. パスワード: `admin123`
3. ログイン成功後、ダッシュボードが表示されるか確認

---

## 📝 使い方

### ブログ記事の投稿方法

#### 1. 管理画面にログイン
```
https://kaleidochrome.com/admin_kc/
```

#### 2. 記事を作成
1. 左メニュー「記事管理」をクリック
2. 「新規記事作成」ボタンをクリック
3. タイトル、本文を入力
4. アイキャッチ画像を設定する場合：
   - FTPで `uploads/` に画像をアップロード
   - 「アイキャッチ画像ファイル名」欄にファイル名を入力（例: `image.jpg`）
5. ステータスを「公開」に設定
6. 「保存」ボタンをクリック

#### 3. 記事が公開される
```
https://kaleidochrome.com/blog.php
```
で記事一覧に表示されます。

---

### 画像の登録方法

#### 1. FTPで画像をアップロード
`uploads/` ディレクトリに画像ファイルをアップロード

#### 2. 管理画面で登録
1. 左メニュー「画像管理」をクリック
2. ファイル名を入力（例: `sample.jpg`）
3. 代替テキスト（任意）を入力
4. 「登録」ボタンをクリック

#### 3. 記事で使用
記事作成時に「アイキャッチ画像ファイル名」欄に登録したファイル名を入力

---

## 🔐 セキュリティ対策

### 必須作業

#### 1. 管理者パスワードの変更
初回ログイン後、必ず以下のSQLを実行してパスワードを変更：

```sql
UPDATE admin_users 
SET password = '$2y$10$新しいハッシュ値' 
WHERE username = 'admin';
```

または、新しい管理者を作成して初期アカウントを削除。

#### 2. デバッグモードの無効化
`includes/config.php` の以下の行を変更：

```php
// 変更前
define('DEBUG_MODE', true);

// 変更後
define('DEBUG_MODE', false);
```

#### 3. エラー表示の無効化
`includes/config.php` の以下の行をコメントアウト：

```php
// 以下をコメントアウト
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
```

#### 4. 管理画面URLの秘匿
- `/admin_kc/` のURLは第三者に教えない
- 必要に応じてBasic認証を追加

---

## 🛠️ トラブルシューティング

### データベース接続エラー
**症状**: 「データベース接続エラーが発生しました」と表示される

**対処法**:
1. `includes/config.php` のDB接続情報を確認
2. お名前ドットコムのコントロールパネルでDB情報を再確認
3. ホスト名、DB名、ユーザー名、パスワードが正しいか確認

### 画像が表示されない
**症状**: ブログ記事のアイキャッチ画像が表示されない

**対処法**:
1. FTPで `uploads/` に画像がアップロードされているか確認
2. ファイル名が正しいか確認（大文字小文字も区別される）
3. `uploads/` ディレクトリのパーミッションを確認（755または777）

### 管理画面にログインできない
**症状**: 「ユーザー名またはパスワードが正しくありません」と表示される

**対処法**:
1. ユーザー名: `admin`
2. パスワード: `admin123`（初期設定）
3. phpMyAdminで `admin_users` テーブルを確認

---

## 📂 ファイル構成

```
/home/zono/v_production/
├── admin_kc/                    # 管理画面
│   ├── index.php               # ログインページ
│   ├── dashboard.php           # ダッシュボード
│   ├── posts.php               # 記事管理
│   ├── images.php              # 画像管理
│   └── logout.php              # ログアウト
├── includes/                    # 共通PHPファイル
│   ├── config.php              # 設定ファイル
│   ├── db.php                  # DB接続
│   └── functions.php           # 共通関数
├── sql/                         # SQLスクリプト
│   └── setup.sql               # 初期テーブル作成
├── uploads/                     # 画像保存先
├── images/                      # 既存画像
├── blog.php                     # ブログ一覧
├── blog_detail.php              # ブログ詳細
├── index.html                   # トップページ
├── talents.html                 # タレント紹介
├── liver.html                   # Vライバーとは
├── linkup.html                  # 個人配信者向け
├── check.html                   # 応募者向け
├── privacy.html                 # プライバシーポリシー
├── styles.css                   # スタイルシート
├── script.js                    # JavaScript
├── .htaccess                    # URL書き換え
├── DEVELOPMENT.md               # 開発メモ
└── README_SETUP.md              # このファイル
```

---

## 🔄 元に戻す方法

静的HTMLサイト（v1.0）に戻したい場合：

### 削除するファイル・ディレクトリ
```bash
admin_kc/
includes/
sql/
uploads/
blog.php
blog_detail.php
DEVELOPMENT.md
README_SETUP.md
```

### HTMLファイルからブログリンクを削除
各HTMLファイル（index.html, talents.html など）から以下の行を削除：
```html
<li><a href="blog.php" class="nav-link">ブログ</a></li>
<a href="blog.php">ブログ</a>
```

詳細は `DEVELOPMENT.md` を参照してください。

---

## 📞 サポート

問題が発生した場合：
1. `DEVELOPMENT.md` を確認
2. エラーログを確認（お名前ドットコムのコントロールパネル）
3. phpMyAdminでデータベースの状態を確認

---

**作成日**: 2025-11-18
**バージョン**: 2.0

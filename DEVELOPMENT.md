# KaleidoChrome 開発メモ

## プロジェクト概要
KaleidoChrome VTuber事務所の公式サイトをPHP化し、ブログ機能を追加。

---

## 開発環境情報

### サーバー環境（お名前ドットコム）
- **PHPバージョン**: 8.4
- **データベース名**: iofy8_kaleidochrome
- **DBホスト**: mysql1036.onamae.ne.jp
- **文字コード**: utf8mb4
- **DBユーザー**: iofy8_admin
- **DBパスワード**: Masa@0118

---

## バージョン管理

### v1.0 - 静的HTMLサイト（オリジナル）
**日付**: 2025-11-18
**説明**: 静的HTMLで構成された基本サイト

**ファイル構成**:
```
/home/zono/v_production/
├── index.html          # トップページ
├── talents.html        # タレント紹介
├── liver.html          # Vライバーとは
├── linkup.html         # 個人配信者向け
├── check.html          # 応募者向けチェックリスト
├── privacy.html        # プライバシーポリシー
├── styles.css          # スタイルシート
├── script.js           # JavaScript
├── .htaccess          # URL書き換え設定
└── images/            # 画像ディレクトリ
    ├── sampleimage.png
    └── girl.png
```

**特徴**:
- アニメーション背景（カレイドスコープ、光ビーム、浮遊棒）
- レスポンシブデザイン
- プライバシーポリシー対応

---

### v2.0 - PHP化 + CMS機能実装（完了）
**開始日**: 2025-11-18
**完了日**: 2025-11-18
**説明**: 静的サイトをPHP化し、フル機能のCMSを実装。タレント管理、ブログ、検索機能を追加。

**新規追加ファイル**:
```
/home/zono/v_production/
├── includes/              # 共通PHPファイル
│   ├── db.php            # データベース接続（SQLite/MySQL自動切替）
│   ├── config.php        # 本番環境設定
│   ├── config_local.php  # ローカル環境設定
│   └── functions.php     # 共通関数（認証、CSRF、日付など）
├── database/             # SQLiteデータベース（ローカル開発用）
│   └── kaleidochrome.db
├── admin_kc/             # 管理画面（推測されにくいパス）
│   ├── index.php         # ログインページ
│   ├── dashboard.php     # ダッシュボード
│   ├── posts.php         # 記事管理（タレントタグ機能付き）
│   ├── talents.php       # タレント管理（登録・編集）
│   ├── images.php        # 画像ファイル名管理
│   └── logout.php        # ログアウト
├── blog.php              # ブログ一覧（4-3-2グリッド）
├── blog_detail.php       # ブログ詳細（タレントタグ表示）
├── talents.php           # タレント一覧（検索機能付き、4-3-2グリッド）
├── uploads/              # アップロード画像保存先
├── sql/                  # SQLスクリプト
│   ├── setup_sqlite.sql  # SQLite用初期化
│   ├── setup.sql         # MySQL用初期化
│   ├── create_talents_table.sql           # タレントテーブル
│   ├── create_post_talents_table.sql      # 記事タレント中間テーブル
│   └── add_name_kana_to_talents.sql       # タレント名（かな）追加
├── setup_local.php       # ローカル環境セットアップ
└── README_SETUP.md       # セットアップ手順
```

**データベーステーブル**:
- `admin_users`: 管理者アカウント（パスワードハッシュ化）
- `posts`: ブログ記事（タイトル、本文、画像、ステータス、閲覧数）
- `talents`: タレント情報（名前、かな、画像、タグ、詳細）
- `post_talents`: 記事とタレントの関連（中間テーブル、最大50件）
- `images`: 画像ファイル名管理

**主要機能**:

#### 1. タレント管理システム
- タレント登録・編集・削除
- フィールド：
  - タレント名、タレント名（かな）
  - 画像ファイル名
  - あかさたなタグ（あ～わ行）
  - フリーワードタグ（カンマ区切り）
  - キャッチフレーズ、詳細

#### 2. タレント検索機能
- あかさたな検索（プルダウン、自動検索）
- フリーワードタグ検索（オートコンプリート、自動検索）
- 検索結果の動的フィルタリング
- 4列（PC） / 3列（タブレット） / 2列（スマホ）グリッド表示

#### 3. ブログシステム
- 記事作成・編集・削除
- タレントタグ機能（最大50件）
  - プルダウンでタレント選択
  - 追加ボタンでタグ追加
  - ピル型タグ表示、×ボタンで削除
- 改行自動変換（nl2br）
- HTMLタグ使用可能
- ピクトグラムアイコン（カレンダー、目）
- 4列（PC） / 3列（タブレット） / 2列（スマホ）グリッド表示
- 背景ぼかし効果（backdrop-filter）

#### 4. 管理画面
- セッション認証
- CSRF対策
- ダッシュボード（統計表示）
- 記事管理（下書き/公開）
- タレント管理
- 画像ファイル名管理

#### 5. デザイン
- レスポンシブ対応
- アニメーション背景（既存維持）
- グラスモーフィズム（ぼかし背景）
- グリッドレイアウト最適化
- ホバーエフェクト

---

## 重要な技術仕様

### データベース自動切替
ローカル（SQLite）と本番（MySQL）を自動で切り替え：
```php
// database/kaleidochrome.db の存在で判定
$isLocal = file_exists(__DIR__ . '/../database/kaleidochrome.db');
```

### 検索ロジック
フリーワードタグ検索は4パターンで検索：
```php
// 1. 完全一致（単独タグ）: "ゲーム"
// 2. 先頭一致: "ゲーム, 歌枠"
// 3. 中間一致: "配信, ゲーム, 歌枠"
// 4. 末尾一致: "配信, ゲーム"
```

### タレントタグ管理
- JavaScriptで動的管理（Map使用）
- hidden inputsで送信
- 最大50件制限
- 編集時は既存タグを自動復元

### セキュリティ
- パスワード: `password_hash()` / `password_verify()`
- CSRF: トークン生成・検証
- XSS: `htmlspecialchars()` （h関数）
- SQLインジェクション: プリペアドステートメント
- セッション: `session_regenerate_id()`

### レスポンシブブレークポイント
- PC: デフォルト（4列グリッド）
- タブレット: max-width: 1024px（3列グリッド）
- スマホ: max-width: 768px（2列グリッド）

---

## 開発履歴

### 2025-11-18

#### 午前: 静的サイト完成（v1.0）
- アニメーション背景実装（下から広がる円、浮遊棒、光ビーム）
- ヘッダーメニューのフェードイン実装
- プライバシーポリシーページ作成
- レスポンシブ対応完了

#### 午後～夜: PHP化 & CMS実装（v2.0）

**フェーズ1: 基盤構築**
- データベース設計・構築
- SQLite（ローカル）/MySQL（本番）自動切替システム
- 共通機能実装（DB接続、認証、CSRF、関数）
- ローカル開発環境セットアップスクリプト作成

**フェーズ2: 管理画面**
- ログイン機能（セッション認証）
- ダッシュボード（統計表示）
- 記事管理（作成・編集・削除、下書き/公開）
- 画像ファイル名管理
- タレント管理（登録・編集・削除）

**フェーズ3: タレント機能**
- タレントテーブル設計・構築
  - name_kana（タレント名かな）追加
  - kana_tag（あかさたなタグ）追加
  - free_tags（フリーワードタグ）追加
- タレント検索機能実装
  - あかさたなプルダウン検索
  - フリーワードタグ検索（オートコンプリート）
  - 自動検索（ボタンレス）
- talents.html → talents.php 移行
- グリッドレイアウト最適化（4-3-2）

**フェーズ4: ブログ機能強化**
- 基本ブログ機能実装
- タレントタグ機能追加
  - post_talents 中間テーブル作成
  - タレント選択UI（プルダウン + 追加ボタン）
  - ピル型タグ表示（赤グラデーション）
  - 最大50件制限
- 改行自動変換（nl2br）実装
- 抜粋フィールド削除
- ピクトグラムアイコン実装（カレンダー、目）
- 余白・レイアウト調整
- 背景ぼかし効果（backdrop-filter）
- グリッドレイアウト最適化（4-3-2）

**フェーズ5: デザイン最適化**
- タレント・ブログの表示簡素化（画像+タイトル/名前のみ）
- 余白調整（タイトル、画像、本文）
- 文字色調整（本文を濃く）
- レスポンシブ対応完了

**技術的な改善**
- 検索クエリの最適化（完全一致、前方一致、中間一致、後方一致）
- タレントタグの動的管理（JavaScript）
- フォームバリデーション
- エラーハンドリング
- セキュリティ対策（XSS、SQLインジェクション、CSRF）

---

### v2.1 - SEO対策・ブラウザ表示最適化（完了）
**実装日**: 2025-01-19
**説明**: SEO対策とブラウザ表示最適化を実装。SNSシェア対応、PWA対応、検索エンジン最適化を完了。

**新規追加ファイル**:
```
/home/zono/v_production/
├── manifest.json          # PWA対応マニフェスト
├── robots.txt            # 検索エンジンクロール制御
└── sitemap.xml           # サイトマップ（Google検索用）
```

**実装内容**:

#### 1. OGP（Open Graph Protocol）タグ
全HTMLファイル（index.html, talents.html, liver.html, linkup.html）に追加：
- `og:site_name` - サイト名
- `og:title` - ページタイトル
- `og:description` - ページ説明
- `og:type` - コンテンツタイプ（website）
- `og:url` - 正規URL
- `og:image` - SNSシェア用画像（1200x630推奨）
- `og:locale` - 言語設定（ja_JP）

**効果**: Twitter、Facebook等でシェアされた時にリッチカード表示

#### 2. Twitter Cardタグ
- `twitter:card` - カード形式（summary_large_image）
- `twitter:title` - タイトル
- `twitter:description` - 説明文
- `twitter:image` - 画像URL

**効果**: Twitterでのシェア時に大きな画像付きカード表示

#### 3. 構造化データ（JSON-LD）
index.htmlに組織情報の構造化データを追加：
```json
{
  "@context": "https://schema.org",
  "@type": "Organization",
  "name": "KaleidoChrome",
  "alternateName": "カレイドクローム",
  "url": "https://kaleidochrome.com",
  "description": "個性が輝く無限の可能性 - KaleidoChrome VTuber事務所"
}
```

**効果**: Google検索結果でリッチスニペット表示の可能性

#### 4. Canonical URL
全ページに正規URLを指定（重複コンテンツ防止）
```html
<link rel="canonical" href="https://kaleidochrome.com/">
```

#### 5. Favicon・アイコン類
各種デバイス向けアイコン設定を追加：
- `favicon.ico` - 標準Favicon
- `favicon-16x16.png` - 16x16サイズ
- `favicon-32x32.png` - 32x32サイズ
- `apple-touch-icon.png` - iPhoneホーム画面用（180x180）
- `android-chrome-192x192.png` - Android用（192x192）
- `android-chrome-512x512.png` - Android用（512x512）

**効果**: ブラウザタブ、ブックマーク、スマホホーム画面にアイコン表示

#### 6. PWA（Progressive Web App）対応
`manifest.json` を作成：
```json
{
  "name": "KaleidoChrome - カレイドクローム",
  "short_name": "KaleidoChrome",
  "start_url": "/",
  "display": "standalone",
  "theme_color": "#dc143c",
  "icons": [...]
}
```

**効果**: スマホでアプリのように動作、ホーム画面に追加可能

#### 7. Theme Color（Android）
アドレスバーの色をブランドカラーに統一：
```html
<meta name="theme-color" content="#dc143c">
```

**効果**: Androidでアドレスバーが赤色（#dc143c）に

#### 8. robots.txt
検索エンジンのクロール制御：
- 管理画面（/admin_kc/）を除外
- データベースファイルを除外
- 一時ファイルを除外
- Sitemap URLを指定

**効果**: 不要なページのインデックスを防止、クロール効率向上

#### 9. sitemap.xml
サイト構造を検索エンジンに通知：
- 全ページのURL一覧
- 更新頻度（changefreq）
- 優先度（priority）
- 最終更新日（lastmod）

**効果**: Google検索のインデックス速度向上

#### 10. 画像の遅延読み込み
HTMLの画像タグに `loading="lazy"` 属性を追加：
```html
<img src="images/girl.png" alt="..." loading="lazy">
```

**効果**: ページ読み込み速度向上、帯域節約

#### 11. パフォーマンス最適化
外部リソースへのプリコネクト：
```html
<link rel="preconnect" href="https://forms.office.com">
```

**効果**: 外部フォームの読み込み高速化

**技術仕様**:
- OGP画像推奨サイズ: 1200x630px
- Favicon生成ツール推奨: https://realfavicongenerator.net/
- Twitter Card検証: https://cards-dev.twitter.com/validator
- Facebook OGP検証: https://developers.facebook.com/tools/debug/

**今後の作業**:
- [ ] OGP用画像（/images/ogp.png）の作成・配置
- [ ] Favicon各種サイズの生成・配置
- [ ] Google Search ConsoleにSitemap登録（任意）

---

### v2.2 - セキュリティ強化・コード整理・URLクリーン化（完了）
**実装日**: 2025-01-19
**説明**: 重大なセキュリティ脆弱性を修正し、不要ファイルを削除、URLをクリーン化。

**🚨 セキュリティ対策（緊急対応完了）**:

#### 1. DB認証情報の環境変数化
**問題**: データベース接続パスワードがconfig.phpに平文でハードコード、GitHubに公開

**対策**:
- `.env` ファイルに認証情報を移動
- `env_loader.php` を作成（環境変数ローダー）
- `config.php` を環境変数読み込み形式に変更
- `.gitignore` に `.env` を追加（既存設定を確認）
- `.env.example` をテンプレートとして作成

**実装内容**:
```php
// 環境変数から取得
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'kaleidochrome');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');
```

#### 2. Git履歴からパスワード完全削除
**問題**: 過去の全コミット履歴にパスワードが含まれている

**対策**:
- `git filter-branch` で全履歴（61コミット）を書き換え
- パスワード文字列を `REDACTED_PASSWORD` に置換
- Git履歴をクリーンアップ（reflog、gc --prune）
- GitHub に強制プッシュ（force push）

**結果**: GitHubの履歴からパスワードが完全削除

#### 3. デバッグモードの環境変数化
**問題**: 本番環境でもデバッグモードがON、エラー情報が露出

**対策**:
- DEBUG_MODE を環境変数で制御
- .env で true/false を切り替え可能に
- 本番環境では自動的にエラー表示OFF

```php
if (DEBUG_MODE) {
    ini_set('display_errors', 1);
} else {
    ini_set('display_errors', 0);
}
```

#### 4. .htaccess セキュリティ強化
.envファイルとデータベースファイルへのアクセスを拒否：
```apache
<Files .env>
    Order allow,deny
    Deny from all
</Files>

<FilesMatch "\.(db|sqlite|sqlite3)$">
    Order allow,deny
    Deny from all
</FilesMatch>
```

---

**不要ファイルの削除**:

削除したファイル（8ファイル）:
- `talents.html` - 旧バージョン（talents.phpに統一）
- `check/index.html` - 重複（check.htmlと同一）
- `liver/index.html` - 未使用
- `add_slug_migration.php` - マイグレーション完了済み
- `add_registration_date_migration.php` - マイグレーション完了済み
- `add_talent_code_migration.php` - マイグレーション完了済み
- `assign_talent_codes.php` - 初期化スクリプト（不要）
- `setup_local.php` - ローカルセットアップスクリプト（不要）

**削減**: 945行のコード削除

---

**URLクリーン化（.htaccess）**:

#### 実装したURLリライトルール
```
/talents              → talents.php（一覧）
/talents/[slug]       → talent_detail.php?slug=[slug]（詳細）
/blog                 → blog.php（一覧）
/blog/[slug]          → blog_detail.php?slug=[slug]（詳細）
```

#### 具体例
**変更前**:
- `https://kaleidochrome.com/talent_detail.php?slug=yamada-taro`
- `https://kaleidochrome.com/blog_detail.php?slug=my-article`

**変更後**:
- `https://kaleidochrome.com/talents/yamada-taro`
- `https://kaleidochrome.com/blog/my-article`

**メリット**:
- SEO向上（検索エンジンフレンドリー）
- URL の可読性向上
- ユーザー体験の改善

---

**技術仕様**:

**環境変数の使い方**:
```bash
# .env ファイル（本番環境・ローカル環境で個別管理）
DB_HOST=mysql1036.onamae.ne.jp
DB_NAME=iofy8_kaleidochrome
DB_USER=iofy8_admin
DB_PASS=your_password_here
DEBUG_MODE=false  # 本番環境では必ず false
```

**Git履歴書き換え詳細**:
- 使用ツール: `git filter-branch --tree-filter`
- 対象: 全61コミット
- 処理時間: 約3秒
- 書き換え後: force push で履歴上書き

---

**⚠️ 重要な注意事項**:

1. **データベースパスワードは必ず変更してください**
   - お名前ドットコムの管理画面から変更
   - 変更後、.env ファイルも更新

2. **本番環境への展開時**
   - `.env` ファイルを本番サーバーに手動配置
   - `DEBUG_MODE=false` に設定
   - パーミッション設定: `.env` は 600 推奨

3. **他のPCで開発する場合**
   - `git pull` 後、`.env.example` をコピーして `.env` を作成
   - 各自の環境に合わせて設定

4. **Git履歴の強制更新について**
   - 他のPCでクローンしている場合、`git pull --force` が必要
   - 既存のローカルブランチは削除して再取得推奨

---

**セキュリティチェックリスト（完了）**:
- [x] DB認証情報の環境変数化
- [x] Git履歴からパスワード削除
- [x] .htaccess で .env ファイル保護
- [x] .htaccess で DB ファイル保護
- [x] デバッグモードの環境変数化
- [x] 不要ファイルの削除

**セキュリティチェックリスト（要対応）**:
- [ ] データベースパスワードの変更（ユーザー対応）
- [ ] 本番環境での .env 配置確認

---

## 🔴 重要リマインダー: データベースパスワード変更

### なぜ変更が必要？
旧パスワード「`Masa@0118`」は過去にGitHubで公開されていました。
- Git履歴からは**完全削除済み**（2025-01-19対応完了）
- 現在のGitHubには露出していない
- **しかし**、過去にリポジトリを閲覧した人がパスワードを知っている可能性がある

### 変更手順（お名前ドットコムで実施）

#### 1. お名前ドットコムにログイン
URL: https://www.onamae.com/

#### 2. データベース設定に移動
- コントロールパネル → 「レンタルサーバー」
- 「データベース設定」または「MySQL設定」を選択

#### 3. パスワード変更
- **データベース名**: `iofy8_kaleidochrome`
- **ユーザー名**: `iofy8_admin`
- **現在のパスワード**: `Masa@0118`
- 「パスワード変更」ボタンをクリック
- 新しい強力なパスワードを入力（12文字以上、英数字記号混在推奨）

#### 4. .env ファイルを更新
パスワード変更後、以下のファイルを更新：

**ローカル環境**: `/home/zono/v_production/.env`
```bash
DB_PASS=新しいパスワード
```

**本番環境**: サーバー上の `.env` ファイルも同様に更新

#### 5. 動作確認
- ローカル: PHP開発サーバーを再起動して接続確認
- 本番: サイトが正常に動作するか確認

### 緊急度
- **優先度**: 中〜高
- **推奨期限**: 数日以内
- Git履歴は削除済みなので新たな漏洩はないが、念のため早めの変更を推奨

### 変更完了後のチェックリスト
- [ ] お名前ドットコムでパスワード変更完了
- [ ] ローカルの .env ファイル更新
- [ ] 本番環境の .env ファイル更新（デプロイ時）
- [ ] ローカル環境での接続確認
- [ ] 本番環境での接続確認（デプロイ後）

---

## TODO

### 完了項目
- [x] データベース構築（SQLite/MySQL両対応）
- [x] DB接続ファイル作成
- [x] セッション管理
- [x] 共通関数作成
- [x] ログイン機能
- [x] ダッシュボード
- [x] 記事管理（CRUD、タレントタグ機能）
- [x] 画像ファイル名管理
- [x] タレント管理（CRUD、検索機能）
- [x] ブログ一覧表示
- [x] ブログ詳細表示
- [x] タレント検索機能
- [x] グリッドレイアウト最適化（4-3-2）
- [x] レスポンシブ対応
- [x] ローカルテスト
- [x] SEO対策（OGP、Twitter Card、構造化データ）
- [x] PWA対応（manifest.json）
- [x] robots.txt / sitemap.xml 作成
- [x] 画像遅延読み込み実装
- [x] DB認証情報の環境変数化
- [x] Git履歴からパスワード完全削除
- [x] 不要ファイル削除（8ファイル）
- [x] URLクリーン化（.htaccess リライト）
- [x] セキュリティ強化（.env保護、デバッグモード制御）

### 未完了項目
- [ ] **データベースパスワードの変更（重要）**
  - [ ] お名前ドットコム管理画面でMySQLパスワード変更
  - [ ] ローカル・本番の .env ファイル更新
- [ ] 本番環境へのデプロイ
  - [ ] MySQLテーブル作成
  - [ ] FTPアップロード
  - [ ] .env ファイル配置（本番用）
  - [ ] 本番環境テスト
  - [ ] 管理者アカウント作成

### 今後の拡張案
- [ ] OGP画像・Favicon画像の作成・配置
- [ ] ブログのタレント絞り込み検索
- [ ] ページネーション改善
- [ ] 記事のカテゴリ機能
- [ ] RSS フィード
- [ ] Google Search Console連携

---

## セキュリティ対策

1. **管理画面**:
   - 推測されにくいURL（/admin_kc/）
   - セッション認証
   - CSRF対策
   - SQLインジェクション対策（プリペアドステートメント）

2. **ファイルアップロード**:
   - 画像は手動FTP経由
   - ファイル名のみをDB管理

3. **データベース**:
   - パスワードハッシュ化（password_hash）
   - XSS対策（htmlspecialchars）

---

## デプロイ手順（本番環境）

### 1. 事前準備
- お名前ドットコムのコントロールパネルでMySQL作成
- DB情報を `includes/config.php` に設定

### 2. ファイルアップロード（FTP）
```
/home/zono/v_production/ の内容を全てアップロード
※ database/ ディレクトリは除く（SQLiteはローカルのみ）
```

### 3. データベース初期化
phpMyAdminまたはSSHで以下を実行：
```sql
-- sql/setup.sql を実行
-- sql/create_talents_table.sql を実行
-- sql/create_post_talents_table.sql を実行
```

### 4. 管理者アカウント作成
```sql
INSERT INTO admin_users (username, password, email) VALUES
('admin', '$2y$10$...', 'admin@example.com');
-- パスワードは事前にハッシュ化して設定
```

### 5. パーミッション設定
```
uploads/ → 755 または 777（書き込み可能に）
```

### 6. 動作確認
- トップページ表示確認
- 管理画面ログイン確認
- ブログ機能確認
- タレント機能確認

## 注意事項

- **既存HTMLファイルは削除・変更しない**
- **新規機能は別ファイルとして追加**
- **データベース接続情報は外部に漏らさない**
- **/admin_kc/ のURLは第三者に教えない**
- **本番環境では必ず HTTPS を使用**
- **定期的なバックアップ（DB + ファイル）**

---

### v2.3 - タレント詳細ページUI/UX改善（完了）
**開始日**: 2025-11-19
**完了日**: 2025-11-19
**説明**: タレント詳細ページと関連記事表示のUI/UX改善、3D画像エフェクト実装

**変更内容**:

#### 1. タレント詳細ページ（talent_detail.php）
**レイアウト調整**:
- PC/タブレット: 画像（左）+ 情報（右）の2カラムレイアウト
- スマホ: 縦一列（画像 → ふりがな → 名前 → キャッチフレーズ → 詳細 → タグ）
- 画像と情報の余白を40px → 13pxに縮小
- スマホ時の画像サイズ: 通常サイズ（max-width: 300px）

**要素の順序とスタイル**:
- ふりがな（14px、グレー）
- タレント名（42px、太字）
- キャッチフレーズ（20px、ピンク #ff69b4、縦棒なし、改行対応）
- 詳細（16px、行間1.7）
- フリーワードタグ
- スマホ時の詳細に左右15pxの余白追加

**余白の調整**:
- ふりがな → 名前: 2px
- 名前 → キャッチフレーズ: 5px
- キャッチフレーズ → 詳細: 5px
- 詳細 → タグ: 15px

**3D画像エフェクト**（NEW）:
- マウス追従の3D回転エフェクトを実装
- タブレット/スマホ（480-768px）: ±15度
- PC（768px超）: ±10度
- 480px以下: エフェクト無効
- CSS: `perspective(1000px)`、`transform-style: preserve-3d`
- JavaScript: マウスムーブでリアルタイム回転、マウスリーブで元に戻る

#### 2. 関連記事セクション
**表示設定**:
- 最大6件表示（`LIMIT 6`）
- 最新順（`ORDER BY published_at DESC`）
- グリッドレイアウト: PC/タブレット 3列、スマホ 2列

#### 3. ブログ詳細ページ（blog_detail.php）
**ブロック統合**:
- タイトル、画像、本文をシームレスに統合
- タイトルブロック: 上のみ角丸（`border-radius: 20px 20px 0 0`）
- 画像: 角丸なし、余白なし
- 本文ブロック: 下のみ角丸（`border-radius: 0 0 20px 20px`）

#### 4. デザイン統一
**背景スタイル**:
- 全ページで統一: `rgba(255, 255, 255, 0.4)`
- ぼかし: `backdrop-filter: blur(5px)`
- トップページのビジョンセクションと同じ透明度・ぼかし

**適用箇所**:
- talent_detail.php: `.talent-main`, `.related-posts-section`
- blog_detail.php: `.article-header`, `.article-content`

#### 5. テストデータ
**タレント10名を追加**:
1. 星空みゆ（歌・ゲーム・ASMR）
2. 桜井ひなた（ゲーム・雑談・料理）
3. 月宮りん（ASMR・読書・お絵描き）
4. 天音かなで（歌・音楽・ピアノ）
5. 水無瀬あおい（ゲーム・FPS・RPG）
6. 紅葉もみじ（雑談・料理・ホラー）
7. 白雪ゆき（ホラー・ASMR・ゲーム）
8. 花園さくら（お絵描き・雑談・イラスト）
9. 夜空める（ゲーム・雑談・深夜配信）
10. 虹色なな（歌・ダンス・ゲーム）

**ブログ記事8件を追加**:
- 日付バラバラ（15日前〜1日前）
- 各記事に関連タレントをタグ付け（1〜10名）
- 閲覧数もランダムに設定

**技術仕様**:
- JavaScript即時関数で3Dエフェクトを実装
- イベントリスナー重複防止（data-initialized属性）
- `willChange: transform`でパフォーマンス最適化
- レスポンシブ対応（デバイスサイズで傾き角度を自動調整）

---

### v2.4 - ブログ予約投稿機能実装（完了）
**開始日**: 2025-11-19
**完了日**: 2025-11-19
**説明**: ブログ記事の予約投稿機能を実装。日時を指定して自動公開できるようになりました。

**変更内容**:

#### 1. 管理画面（admin_kc/posts.php）
**投稿日時入力フィールド追加**:
- `<input type="datetime-local">` を使用したカレンダー＋時刻選択UI
- ステータスを「公開」にした時のみ表示
- デフォルト値: 現在日時
- 必須入力（公開時）

**JavaScriptによる動的表示**:
```javascript
// ステータス変更時に投稿日時フィールドを表示/非表示
statusSelect.addEventListener('change', function() {
    if (this.value === 'published') {
        datetimeGroup.style.display = 'block';
        datetimeInput.setAttribute('required', 'required');
    } else {
        datetimeGroup.style.display = 'none';
        datetimeInput.removeAttribute('required');
    }
});
```

**保存処理の修正**:
- POSTデータから `published_datetime` を取得
- `strtotime()` で MySQL形式に変換
- バリデーション: 公開時は日時必須

#### 2. フロントエンド表示制御
**blog.php（ブログ一覧）**:
```sql
SELECT * FROM posts
WHERE status = 'published'
AND published_at <= datetime('now', 'localtime')
ORDER BY published_at DESC
```

**blog_detail.php（ブログ詳細）**:
```sql
SELECT * FROM posts
WHERE slug = :slug
AND status = 'published'
AND published_at <= datetime('now', 'localtime')
```

**動作**:
- 現在時刻より前の `published_at` を持つ記事のみ表示
- 未来の日時を指定した記事は、その日時まで非表示
- 指定日時になると自動的に公開される

#### 3. 使い方
1. 管理画面で記事作成
2. ステータスを「公開」に変更
3. カレンダーから日付を選択
4. 時刻（時:分）を選択
5. 保存

**例**: 12月1日 20:00に公開したい場合
- 投稿日時: 2025-12-01 20:00
- → 2025年12月1日 20:00になると自動的にサイトに表示される

**注意事項**:
- 過去の日時を指定すると即座に公開されます
- 下書きに戻すと、再度公開時に日時の再指定が必要です
- 編集時は既存の日時が自動入力されます

---

### v2.5 - ナビゲーションメニュー統一・文言修正（完了）
**実装日**: 2025-11-19
**説明**: 全ページのナビゲーションメニューとフッターを統一し、文言を修正

**変更内容**:

#### 1. メニュー順の統一
**新しいメニュー順**:
1. HOME
2. TALENTS
3. BLOG
4. Vライバーとは
5. 個人配信者の方へ
6. お問い合わせ

**変更前**:
```
HOME → TALENTS → Vライバーとは → 個人配信者の方へ → BLOG → お問い合わせ
```

**変更後**:
```
HOME → TALENTS → BLOG → Vライバーとは → 個人配信者の方へ → お問い合わせ
```

#### 2. 英語メニュー項目の大文字化
- `Home` → `HOME`
- `Talents` → `TALENTS`
- `ブログ` → `BLOG`

#### 3. 文言修正
- `Vライバーとは？` → `Vライバーとは`（？を削除）

#### 4. 対象ファイル（9ファイル）
**HTML/PHPファイル**:
- index.html
- talents.php
- blog.php
- blog_detail.php
- talent_detail.php
- liver.html
- linkup.html
- check.html
- privacy.html

**適用箇所**:
- ナビゲーションメニュー（`.nav-menu`）
- フッターリンク（`.footer-links`）

#### 5. linkup.htmlの文言修正
**変更箇所**: 個人配信者向けページのヒーローセクション

**変更前**:
```
カレイドクロームでは、個人で配信活動されている方々のサポートをしております。
```

**変更後**:
```
カレイドクロームでは、個人でYouTube配信活動されている方々のサポートをしております。
```

**理由**: より具体的にYouTube配信者向けであることを明示

#### 6. 技術的な実装
- 全ページで統一した構造を使用
- `nav-link` クラスでアクティブページを判別（`.active`）
- レスポンシブ対応（ハンバーガーメニュー）
- フッターも同様の順序で統一

**効果**:
- サイト全体のナビゲーション統一性向上
- ユーザー体験の改善（一貫したメニュー順）
- ブランディングの統一（英語大文字化）
- 対象ユーザーの明確化（YouTube配信者）

---

### v2.6 - Vision Block Section UI/UX改善（完了）
**実装日**: 2025-11-20
**説明**: トップページ（index.html）にVision Block Sectionを新規作成。3つの横並びブロックに画像とテキストを配置し、レインボーキャッチフレーズと底部画像を追加。

**変更内容**:

#### 1. Vision Block Section 構造
**配置**: 「私たちのビジョン」セクションの上に新規追加

**構成要素**:
- `.vision-block-section`: 外枠コンテナ
- `.vision-block-container`: ガラスモーフィズム背景（`rgba(255, 255, 255, 0.4)`, `blur(5px)`）
- `.vision-block-item`: 3つの横並びブロック
- `.vision-catchphrase`: レインボーキャッチフレーズ
- `.vision-bottom-images`: 底部の3画像エリア

#### 2. 3つの横並びブロック
**ブロック1（左・青）**:
- 画像: `top1.png`
- 斜めテキスト: 「学生さん」（濃い青 #003d82）
- 下部テキスト: 「未経験者<br class="tablet-only">大歓迎♪」

**ブロック2（中央・黄色）**:
- 画像: `top2.png`
- 斜めテキスト: 「社会人」（金色 #d4a017）
- 下部テキスト: 「スキマ時間<br class="tablet-only">を活用！」

**ブロック3（右・緑）**:
- 画像: `top3.png`
- 斜めテキスト: 「主婦の方」（濃い緑 #228b22）
- 下部テキスト: 「主婦の方も<br class="tablet-only">活躍中！」

**背景色**:
- 左: `rgba(173, 216, 230, 0.2)` （ライトブルー）
- 中央: `rgba(255, 255, 153, 0.2)` （イエロー）
- 右: `rgba(144, 238, 144, 0.2)` （ライトグリーン）

#### 3. 画像上の斜めテキスト
**スタイル**:
- フォント: 'Yusei Magic', sans-serif（Google Fonts）
- サイズ: 36px（PC/タブレット）、42px（スマホ）
- 回転: `transform: rotate(15deg)`
- 位置: 右上（`top: 2%, right: 2%`）
- 白縁: `7px white`（`-webkit-text-stroke`）

**Google Fonts追加**:
```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Yusei+Magic&display=swap" rel="stylesheet">
```

#### 4. 画像下のピンクテキスト
**スタイル**:
- フォントサイズ: 24px（PC/タブレット）、28px（スマホ）
- 色: ピンク `#ff69b4`
- 白縁: `2px white`（袋文字）
- 余白: 上下 `5px`（画像との間）

**タブレット用改行**:
- `.tablet-only` クラス: 769px～1200pxで表示
- 例: 「未経験者」→改行→「大歓迎♪」

#### 5. レインボーキャッチフレーズ
**テキスト**: 「あなたもVTuberになれる！」

**スタイル**:
- フォントサイズ: 42px（PC/タブレット）、6vw（スマホ）
- グラデーション: `linear-gradient(90deg, red, orange, yellow, green, cyan, blue, violet, red)`
- アニメーション: 右から左へスクロール（6秒ループ）
- 白影: `filter: drop-shadow(3px 3px 3px rgb(255, 255, 255))`
- 背景クリッピング: `background-clip: text`, `-webkit-text-fill-color: transparent`

**アニメーション**:
```css
@keyframes rainbowScroll {
    0% { background-position: 200% 0%; }
    100% { background-position: 0% 0%; }
}
```

#### 6. 底部の3画像（横並び）
**画像**:
- `topv.png`: 青い影（`rgba(0, 100, 255, 0.6)`）
- `topv2.png`: 黄色い影（`rgba(255, 255, 0, 0.6)`）
- `topv3.png`: 緑の影（`rgba(0, 200, 0, 0.6)`）

**レイアウト**:
- 常に横並び（スマホでも）
- Flexbox: `gap: 20px`（PC/タブレット）、`10px`（スマホ）
- 最大幅: 250px（PC/タブレット）
- 影: `filter: drop-shadow(0 10px 20px rgba(...))`（透明度対応）

#### 7. レスポンシブデザイン
**PC/タブレット（769px以上）**:
- 3ブロック横並び（`flex-wrap: wrap`）
- パディング: 上下 20px、左右 30px

**タブレット（769px～1200px）**:
- `.tablet-only` の `<br>` タグ表示
- テキスト折り返し対応

**スマホ（768px以下）**:
- 3ブロック縦並び（`flex-direction: column`）
- 画像最大幅: 300px
- レインボーテキスト: `6vw`（画面幅に追従）

#### 8. 余白調整（最終）
**Vision Block Item**:
- パディング: `20px 30px`（上下縮小）

**Vision Image Wrapper**:
- マージン下: `5px`（15px → 5px）

**効果**: 画像と下部テキストの間隔を大幅に縮小し、コンパクトなレイアウトを実現

#### 9. 技術的なポイント
**filter: drop-shadow vs box-shadow**:
- `drop-shadow`: 透明部分を除外した影（画像の形に沿った影）
- `box-shadow`: 要素の矩形全体に影
- 底部画像は透明PNGのため `drop-shadow` を使用

**背景クリッピング**:
```css
background: linear-gradient(...);
background-clip: text;
-webkit-background-clip: text;
-webkit-text-fill-color: transparent;
```
テキストにグラデーションを適用する手法

**袋文字（Outlined Text）**:
```css
-webkit-text-stroke: 2px white;
text-stroke: 2px white;
paint-order: stroke fill;
```

**Google Fonts読み込み**:
- `rel="preconnect"` で高速化
- `crossorigin` 属性で CORS 対応

---

### v2.7 - オープニングアニメーション・UI改善（完了）
**実装日**: 2025-12-07
**説明**: トップページにオープニングアニメーション追加、バーガーメニューのUI改善、サークル応募ボタン実装

**変更内容**:

#### 1. オープニングアニメーション
**構成要素**:
- `.opening-animation`: 固定オーバーレイ（z-index: 9999）
- `.opening-image-container`: 画像コンテナ（3D回転付き）
- `.opening-image`: iriam_image.webp表示

**アニメーション効果**:
- 画像表示: perspective(800px) rotateY(-12deg) で右に傾斜
- フェードイン: 0.2秒
- フロート: 1.0秒（上下に軽く浮遊）
- フェードアウト: 0.3秒（1.1秒後開始、拡大しながら手前に消える）

**連動フェードイン**:
- トップ画像: 1.6秒後にフェードイン開始
- キャッチフレーズ: 1.9秒後にフェードイン開始
- ライバー募集ボタン: 2.0秒後にフェードイン開始（スケールアニメーション付き）

**JavaScript制御**:
- 画像ロード待機（`openingImage.complete`チェック）
- ロード完了後に`.loaded`クラス追加でアニメーション開始
- 1.4秒後に`.hidden`クラス追加で非表示

#### 2. ヒーローキャッチフレーズ変更
**変更前**: 「あなたらしさ×万華鏡」

**変更後**: 「いま、稼ぐならVライバー」
- 「いま、」「なら」: 赤色（#ff0000）
- 「稼ぐ」「Vライバー」: 白色（#ffffff）
- ルビ対応: 「稼」→「かせ」、「V」→「ブイ」

#### 3. バーガーメニューUI改善（Razz.jp参考）
**スライド方向**: 右から左へスライドイン

**斜めカット背景**:
```css
clip-path: polygon(65% 0, 100% 0, 100% 100%, 15% 100%);
```
- 右上から左下へ鋭角にカット（上部2/3位置）
- 背景: ぼかし効果付き（`backdrop-filter: blur(10px)`）

**Z-index階層**:
- nav-overlay: 999（クリックで閉じる用）
- nav: 1000
- nav-menu: 1002
- hamburger: 1003（×ボタンが前面に来るよう修正）

**ハンバーガーボタン**:
- 三本線の色: 赤色（`var(--red)`）

**Xボタン追加**:
- ライバー募集ボタンの左に配置
- サイズ: 50x50px（小さめ）
- 背景: 薄いグレー（`rgba(128, 128, 128, 0.4)`）、フチなし
- リンク: `https://x.com/kaleidochrome`
- 下揃え（`align-items: flex-end`）、gap: 30px

**ヘッダー透明化**:
- 常に透明（`background: transparent`）
- スクロール時も透明を維持

#### 4. サークル応募ボタン（水彩グラデーション）
**バーガーメニュー内ボタン（.nav-apply-circle）**:
- 位置: メニュー下部
- 表示: モバイル時のみ（`.mobile-only`）

**フローティングボタン（.floating-application-btn）**:
- 位置: 右下固定（bottom: 30px, right: 30px）
- サイズ: 120x120px（PC）、100x100px（タブレット）、90x90px（スマホ）

**デザイン**:
- 水彩風グラデーション（紫・青・赤）
```css
background:
    radial-gradient(ellipse at 30% 20%, rgba(138, 43, 226, 0.8) 0%, transparent 50%),
    radial-gradient(ellipse at 70% 30%, rgba(30, 144, 255, 0.8) 0%, transparent 50%),
    radial-gradient(ellipse at 50% 70%, rgba(220, 20, 60, 0.8) 0%, transparent 50%),
    radial-gradient(ellipse at 20% 80%, rgba(75, 0, 130, 0.6) 0%, transparent 40%),
    radial-gradient(ellipse at 80% 80%, rgba(0, 191, 255, 0.6) 0%, transparent 40%),
    linear-gradient(135deg, #8b5cf6 0%, #3b82f6 25%, #ec4899 50%, #8b5cf6 75%, #3b82f6 100%);
```

**回転テキスト**:
- テキスト: 「Become a V-Liver! Become a V-Liver!」
- SVG textPathで円周上に配置
- 10秒で1回転（`@keyframes rotate-text-floating`）

**中央テキスト**: 「ライバー<br>募集中！」

**リンク先**: `https://forms.office.com/r/N1cAFSeNu0`

#### 5. check.htmlリンク削除
- 全ページからcheck.htmlへのリンクを削除
- 応募ボタンはすべてMicrosoft Formsへ直接リンク

#### 6. 対象ファイル
**HTML/PHP**:
- `index.html`: オープニングアニメーション、バーガーメニュー、サークルボタン
- `liver.html`: サークルボタン更新
- `linkup.html`: サークルボタン更新

**CSS（styles.css）**:
- オープニングアニメーションスタイル
- バーガーメニュー斜めカット（clip-path）
- ヘッダー透明化
- サークルボタンスタイル（水彩グラデーション、回転テキスト）
- レスポンシブ対応（768px、480px）

**JavaScript（script.js）**:
- オープニングアニメーション制御
- nav-overlayクリックでメニュー閉じる

---

### v2.8 - トップページ構成変更・軽量化（完了）
**実装日**: 2025-12-07
**説明**: トップページの構成を大幅変更。不要なセクション・画像を削除してサイト軽量化。IRIAMバッジセクション追加。

**変更内容**:

#### 1. 削除したセクション・画像
**Vision Blockセクション削除**:
- 「学生さん / 社会人 / 主婦の方」の3ブロック削除
- 「あなたもVTuberになれる！」キャッチフレーズ削除
- VTuber画像3枚（topv, topv2, topv3）削除

**削除した画像ファイル（合計約2.5MB削減）**:
- images/topv.webp (510KB)
- images/topv2.webp (489KB)
- images/topv3.webp (505KB)
- images/top1.webp (322KB)
- images/top2.webp (337KB)
- images/top3.webp (304KB)
- images/IRIAM_app_icon.png
- images/logo-iriam.png
- images/iriam_image.png

**削除したフォント**:
- Yusei Magic（Google Fonts）→ Noto Sans JPに統一

#### 2. IRIAMバッジセクション追加
**構成**:
- header.webp（カレイドクロームロゴ）
- 「イリアムライバー事務所」テキスト（黒、Noto Sans JP）
- IRIAMアプリアイコン + ロゴ（https://iriam.com/へリンク）
- 「Vライブコミュニケーションアプリ」テキスト（青）

**レスポンシブ対応**:
- スマホ: 縦並び
- タブレット以上: 横並び（左グループ + 右グループ）

**アイコンスタイル**:
- IRIAMアプリアイコン: 60x60px、角丸15px（スマホアプリ風）
- header.webp: 50px高さ

#### 3. ヘッダーメニュー色変更
- ナビゲーションリンク色: 白 → 赤（`var(--red)`）
- スクロール時の背景変更を廃止（常に透明）

#### 4. オープニングアニメーション調整
- IRIAM画像を時計回りに15度傾斜（`rotateZ(15deg)`）
- フェードアウト時に傾きが0度に戻る

#### 5. キャッチフレーズ補足追加
- 「いま、稼ぐならVライバー※」（※は右上に配置）
- 補足: 「※イラストと声でするライブ配信者です。」
- 余白を詰めてコンパクトに

#### 6. 「KaleidoChrome」→「カレイドクローム」表記統一
**変更箇所**:
- index.html: 「私たちのビジョン」セクション
- liver.html: 「カレイドクロームの魅力」、所属ステップ、応募セクション
- blog.php: ブログヒーロー説明文

#### 7. 余白調整
- IRIAMバッジセクション → 「私たちのビジョン」間の余白縮小
- aboutセクションのpadding-top: 100px → 20px

#### 8. プライバシーポリシーリンク色
- index.htmlお問い合わせセクション: グレー → 白

**技術的改善**:
- サイト容量約2.5MB削減
- Google Fonts読み込み軽量化（Yusei Magic削除）
- DOM要素削減（Vision Blockセクション分）

---

### v2.9 - Vライバーの魅力セクションUI改善（完了）
**実装日**: 2025-12-07
**説明**: 「Vライバーの魅力」セクションをブロックデザインに変更。展開式詳細、左線カラー、斜めカットデザインを実装。

**変更内容**:

#### 1. ブロックデザインに変更
**旧デザイン**:
- 絵文字（🟠🟢🔵）付きリスト形式
- グレー背景に白文字（読みにくい）

**新デザイン**:
- 白背景ブロック（`#fff`）
- 左に4pxの色付きボーダー
- 右下を斜めにカット（`clip-path`）

#### 2. 3色の左ボーダー
**各項目の色分け**:
- スマホひとつで活動できる！: オレンジ（`#ff9800`）
- 顔バレしない！: 緑（`#4caf50`）
- 配信・交流が楽しい！: 青（`#2196f3`）

**見出し文字色**: 左ボーダーと同じ色

#### 3. 展開式詳細機能
**「もっと詳しく」ボタン**:
- 位置: ブロック右上
- 背景色: 濃いグレー（`#333`）
- サイズ: 小さめ（`padding: 5px 12px`, `font-size: 11px`）

**クリック動作**:
- 各項目の下に詳細テキストが展開
- ボタンテキストが「閉じる」に変化
- 再クリックで閉じる

**詳細テキストスタイル**:
- 色: 黒（`#333`）
- フォント: Noto Sans JP、font-weight: 400
- 左に34pxのpadding（インデント）

#### 4. 斜めカットデザイン
**clip-path実装**:
```css
clip-path: polygon(0 0, 100% 0, 100% calc(100% - 15px), calc(100% - 15px) 100%, 0 100%);
```
- 右下コーナーを15px斜めにカット

#### 5. レイアウト
**閉じている時**: 中央寄り（`display: inline-flex`）
**展開時**: 詳細が各項目の直下に表示

---

## 汎用コンポーネント

### 展開ブロック（Expandable Block）
**場所**: styles.css / script.js
**用途**: 見出しクリックで詳細が展開するUI。複数セクションで再利用可能。

#### HTML構造
```html
<!-- 全展開ボタン（任意） -->
<button class="detail-toggle-btn" data-group="グループ名" onclick="toggleAllExpandables(this)">もっと詳しく</button>

<!-- ブロックリスト -->
<div class="expandable-list" data-group="グループ名">
    <div class="expandable-item border-色" onclick="toggleExpandable(this)">
        <div class="expandable-summary">見出し</div>
        <div class="expandable-detail">詳細テキスト</div>
    </div>
    <!-- 繰り返し -->
</div>
```

#### 左線カラーバリエーション
| クラス | 色 | HEX |
|--------|------|---------|
| `border-orange` | オレンジ | #ff9800 |
| `border-green` | 緑 | #4caf50 |
| `border-blue` | 青 | #2196f3 |
| `border-red` | 赤 | #dc143c |
| `border-purple` | 紫 | #9c27b0 |
| `border-pink` | ピンク | #e91e63 |

#### JavaScript関数
| 関数 | 用途 |
|------|------|
| `toggleExpandable(element)` | 個別ブロックの開閉 |
| `toggleAllExpandables(btn)` | 全ブロック一括開閉 |
| `updateExpandableButtonText(group)` | ボタンテキスト自動更新 |

#### data-group属性
- 同じ`data-group`値を持つリストとボタンが連動
- 複数セクションで別々のグループを設定可能
- 例: `data-group="vliber"`, `data-group="vision"`, `data-group="faq"`

#### 特徴
- **個別クリック**: そのブロックだけ開閉
- **全展開ボタン**: 全て開く/閉じる
- **ボタンテキスト自動更新**: 全て開いている時だけ「閉じる」表示
- **ホバーエフェクト（PC）**: 右に3pxスライド（スマホではタップで動作）
- **斜めカット**: 右下15pxカット（clip-path）

#### デザイン
- 背景: 白（#fff）
- 見出し色: 左線と同色
- 詳細文字色: 黒（#333）
- フォント: Noto Sans JP、font-weight: 400

---

### v2.10 - ヘッダー常時バーガーメニュー化・タイトル白縁追加（完了）
**実装日**: 2025-12-07
**説明**: ヘッダーを常時バーガーメニュー表示に変更。「Vライバーの魅力」タイトルに白縁を追加。

**変更内容**:

#### 1. ヘッダー常時バーガーメニュー化
**変更前**: PC時はテキストメニュー、スマホ時のみバーガーメニュー

**変更後**: 全デバイスで常にバーガーメニュー表示

**CSS変更**:
- `.nav-menu`: 常に`position: fixed`, `right: -100%`で画面外に配置
- `.nav-menu.active`: `right: 0`でスライドイン
- `.hamburger`: 常に`display: flex`
- メディアクエリ内の重複スタイル削除

**修正した問題**:
- 回転テキスト（`.rotating-text`）がPCで表示されない → デフォルトスタイルに移動
- `.nav-apply-circle`に`position: relative`追加（回転テキストの親要素として必要）
- `.nav-overlay`のデフォルトスタイル追加

#### 2. 「Vライバーの魅力」タイトルに白縁追加
**適用箇所**: `.about-text h3`

**スタイル**:
```css
text-shadow:
    -1px -1px 0 #fff,
    1px -1px 0 #fff,
    -1px 1px 0 #fff,
    1px 1px 0 #fff;
```

**効果**: 背景が暗い場所でも赤いタイトルが見やすくなる

#### 3. 削除した画像
- `images/topv4.webp`: 未使用画像を削除

---

### v2.11 - レイアウト調整・フォント統一（完了）
**実装日**: 2025-12-07
**説明**: グレーブロックの横幅調整、展開時の横幅拡大、私たちのビジョンの白ブロック化、フッターのフォント統一

**変更内容**:

#### 1. グレーブロック（.about-text）の横幅変更
- `max-width: 600px` → `max-width: 800px`

#### 2. 白ブロック（.expandable-list）の展開時横幅拡大
- 閉じている時: `max-width: 400px`
- 開いている時: `max-width: 700px`（`:has(.expandable-detail.show)`で判定）
- アニメーション: `transition: max-width 0.3s ease`

#### 3. 詳細テキストのフォントサイズ変更
- `.expandable-detail`: `font-size: 14px` → `font-size: 16px`

#### 4. タイトル白縁の細さ調整
- `.about-text h3`: `text-shadow` を `1px` → `0.6px` に変更

#### 5. 「私たちのビジョン」を白ブロックに変更
- 同じAbout Section内に移動（余白統一のため）
- 2つの白ブロック（ピンク・紫）で構成
  - **ピンク**: 安心して稼げるIRIAM事務所
  - **紫**: 無限の可能性
- 詳細は最初から展開状態（`show`クラス付き）

#### 6. お問い合わせセクションの調整
- `padding-top: 0` で上の余白を詰める
- `.contact-info p`: `font-weight: 600` → `font-weight: 400`（Noto Sans JP）

#### 7. フッターのフォント統一
- `.footer`: `font-family: 'Noto Sans JP', sans-serif` を追加
- 全ページで統一されたフォント表示

#### 8. フッターXボタンの背景色変更
- 青グラデーション → グレー（`#666`）
- ホバー時: `#888`
- box-shadowを削除してシンプルに

---

### v2.13 - 全ページUI統一・角丸0化（完了）
**実装日**: 2025-12-08
**説明**: 全ページのカード・ブロックを黒+シルバーグラデーションUIに統一。角丸を0に変更。

**変更内容**:

#### 1. 背景グラデーション変更
- 白→薄グレー から 白→濃い紫（#6b3fa0）に変更

#### 2. about-textブロックのスタイル調整
- タイトル（h3）: Shippori Mincho明朝体、font-weight: 800
- 本文（p）: Noto Sans JP、font-weight: 400
- タイトル下余白: 20px → 10px
- 本文下余白: 20px → 0

#### 3. blog.php
- ヒーローをabout-textブロックに変更
- ブログカード: 黒+シルバーグラデーション、角丸0、タイトル白
- ホバー時の浮き上がり削除
- SQLite/MySQL両対応（datetime関数切り替え）

#### 4. blog_detail.php
- article-header、article-content: 角丸0
- SQLite/MySQL両対応

#### 5. talents.php
- タイトル+検索フォームを一つのabout-textブロックに統合
- タレントカード: 黒+シルバーグラデーション、角丸0、タイトル白
- ホバー時の浮き上がり削除

#### 6. talent_detail.php
- talent-main、related-posts-section: 角丸0
- 関連記事カード: 黒+シルバーグラデーション、タイトル白
- タグとボタンは角丸を維持

#### 7. liver.html
- ヒーローセクション（Vライバーとは）削除
- 「こんな方におすすめ」を一番上に配置、about-textブロック化

---

### v2.12 - グレーブロックシルバーグラデーション・liver.html整理（完了）
**実装日**: 2025-12-08
**説明**: グレーブロックにシルバーグラデーションと影を追加。liver.htmlから重複コンテンツを削除。

**変更内容**:

#### 1. グレーブロック（.about-text）デザイン変更
**背景**:
- `rgba(0,0,0,0.6)` → 真っ黒（`#000`）に変更

**シルバーグラデーション**:
- `::before`擬似要素で左上から右下へのグラデーション
- 左上: 白みがかったシルバー（`rgba(255, 255, 255, 0.7)`）
- 中間: シルバー（`rgba(220, 220, 230, 0.5)`）
- 75%地点で透明に
- `z-index: 0`でコンテンツの後ろに配置

**右下影**:
- `box-shadow: 8px 8px 20px rgba(0, 0, 0, 0.8)`

#### 2. タイトル（.about-text h3）の黒影
- 白影から黒影に変更
- `text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.8)`
- ぼかし少なめの1pxシャドウ

#### 3. liver.htmlから「Vライバーの魅力」セクション削除
**理由**: index.htmlの内容と重複

**削除したセクション**:
- `<!-- VTuber Charm Section -->` (192〜214行目)
- 「配信が楽しい」「顔バレしない」「スマホ一つで活動できる」の3ブロック

**残したセクション**（liver.html独自コンテンツ）:
- こんな方におすすめ
- カレイドクロームの魅力
- 募集要項
- 配信までの流れ
- Vライバー募集中

---

### v2.14 - フォント統一・メニュー改善・ページ分割（完了）
**実装日**: 2025-12-09
**説明**: 全ページをShippori Mincho明朝体に統一。バーガーメニューを斜めスライドイン＋黄色ラインに改善。liver.htmlからagency.htmlを分離。

**変更内容**:

#### 1. フォント統一（Shippori Mincho）
- body全体を`'Shippori Mincho', serif`に変更
- 全ての`font-family: 'Noto Sans JP', sans-serif`を`'Shippori Mincho', serif`に置換
- 各ページ（blog.php, talents.php, talent_detail.php, blog_detail.php, liver.html）にGoogle Fonts読み込み追加

#### 2. バーガーメニュー改善
**斜めカット**:
- 波アニメーション削除
- シンプルなスライドイン＋斜めカット維持
- `clip-path: polygon(65% 0, 100% 0, 100% 100%, 15% 100%)`

**黄色グラデーションライン**:
- `.nav-menu::before`で左端に黄色の細いライン追加
- 上から下へ黄色グラデーション
- `clip-path: polygon(65% 0, 65.5% 0, 15.5% 100%, 15% 100%)`で斜めに配置
- `filter: drop-shadow(0 0 6px rgba(255, 215, 0, 0.8))`で光る効果

#### 3. ページ分割（liver.html → agency.html）
**新規作成: agency.html**:
- 「カレイドクロームの魅力」セクションをliver.htmlから移動
- 5つのサポート内容:
  1. コミュニティが充実
  2. デザインサポート
  3. 配信サポート
  4. グッズ制作
  5. 高い報酬制度

**liver.html変更**:
- 「カレイドクロームの魅力」セクション削除
- 残りのコンテンツ（こんな方におすすめ、募集要項、配信までの流れ等）を維持

#### 4. メニュー名変更
**全ページのナビゲーション/フッター更新**:
- `Vライバーとは` → `Vライバーの魅力`（liver.htmlへ）
- 新メニュー追加: `カレイドクロームの魅力`（agency.htmlへ）

**更新ファイル（10ファイル）**:
- index.html
- liver.html
- agency.html
- linkup.html
- blog.php
- blog_detail.php
- talents.php
- talent_detail.php
- privacy.html
- check.html

---

### v2.15 - UI/UX改善・展開可能ブロック統一（完了）
**実装日**: 2025-12-10
**説明**: 各ページのUI/UX改善。展開可能ブロックの統一、余白調整、テキスト中央寄せ、ブログ詳細の金枠デザイン。

**変更内容**:

#### 1. ブログ詳細ページ（blog_detail.php）デザイン変更
- 背景: 黒+シルバーグラデーション
- タイトル: 黄色グラデーション
- 外枠: 金色ボーダー（上下左右に擬似要素で配置）
- 画像部分: 左右のみ金枠（上下なし）

#### 2. 全ページの黒背景ブロック全幅化
- `.about-text`, `.message-box`, `.requirement-item`, `.process-step`, `.support-message-box`, `.support-grid`, `.charm-grid`
- `width: 100vw` + `margin-left/right: calc(-50vw + 50%)`で画面端まで拡張
- ブログ詳細ページは除外

#### 3. ヘッダーナビゲーション改善
- `.nav-container`: `max-width: 100%`でロゴ/ハンバーガーを画面端に配置
- ハンバーガーメニュー: タブレット以上で大きく（40px幅、3px高さ、8px gap）

#### 4. 展開可能ブロック（expandable-item）統一
- 背景: 透明
- 左線: 金色に統一（カラフル→金色）
- テキスト: 金色グラデーション
- スマホ: タップでモーダル表示
- タブレット以上: 詳細常時表示、「もっと詳しく」ボタン非表示

#### 5. 展開可能モーダル改善
- 「もっと詳しく」ボタン削除（JSで除去）
- リストマーカー（・）削除（`list-style: none`）

#### 6. agency.html改善
- サポートカードを展開可能ブロックに変更（5項目）
- `about-text`ブロック内に配置
- モーダル追加（スマホ用）
- 「Vライバー募集中」見出し追加

#### 7. liver.html改善
- 「どんな配信をする？」セクションをindex.htmlから移動
- 募集要項・応募フローの中央寄せ
- 「一次審査」→「1次審査」表記統一
- 「【応募はこちらから】」→「【ライバー募集中！】」変更
- プロセスステップのホバーエフェクト削除、左赤線削除

#### 8. 余白調整
- `.liver-application`: padding下を60pxに
- `.application-note`: padding下を10pxに
- `.liver-recommend` / `.liver-streaming`: 間の余白縮小（30px）

#### 9. 背景画像位置調整
- `.about-text::after`: 背景画像を下揃え（`right bottom`）

#### 10. お問い合わせボタン統一（.contact-btn）
- 黒背景 + 金色グラデーションボーダー
- 金色グラデーションテキスト
- シャインアニメーション（3秒ループ）
- 適用箇所: index.html, liver.html, agency.html, linkup.html

---

## 連絡先

開発者: Claude Code
プロジェクト: KaleidoChrome Official Website

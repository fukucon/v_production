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
- スマホ時の画像サイズを1.1倍に拡大（max-width: 330px）

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

## 連絡先

開発者: Claude Code
プロジェクト: KaleidoChrome Official Website

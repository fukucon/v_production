-- KaleidoChrome データベース初期セットアップ
-- 作成日: 2025-11-18
-- データベース: iofy8_kaleidochrome

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- ==========================================
-- 1. 管理者アカウントテーブル
-- ==========================================
CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL COMMENT 'password_hash()で暗号化',
  `email` VARCHAR(100) NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `last_login` DATETIME NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='管理者アカウント';

-- ==========================================
-- 2. ブログ記事テーブル
-- ==========================================
CREATE TABLE IF NOT EXISTS `posts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(200) NOT NULL COMMENT '記事タイトル',
  `slug` VARCHAR(200) NOT NULL UNIQUE COMMENT 'URL用スラッグ',
  `content` TEXT NOT NULL COMMENT '記事本文（HTML可）',
  `excerpt` VARCHAR(300) NULL COMMENT '要約・抜粋',
  `featured_image` VARCHAR(255) NULL COMMENT 'アイキャッチ画像ファイル名',
  `status` ENUM('draft', 'published', 'archived') DEFAULT 'draft' COMMENT '公開状態',
  `author_id` INT UNSIGNED NOT NULL COMMENT '投稿者ID',
  `view_count` INT UNSIGNED DEFAULT 0 COMMENT '閲覧数',
  `published_at` DATETIME NULL COMMENT '公開日時',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idx_slug` (`slug`),
  INDEX `idx_status` (`status`),
  INDEX `idx_published_at` (`published_at`),
  INDEX `idx_author` (`author_id`),
  FOREIGN KEY (`author_id`) REFERENCES `admin_users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ブログ記事';

-- ==========================================
-- 3. 画像管理テーブル
-- ==========================================
CREATE TABLE IF NOT EXISTS `images` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `filename` VARCHAR(255) NOT NULL COMMENT '画像ファイル名',
  `original_name` VARCHAR(255) NULL COMMENT '元のファイル名',
  `file_path` VARCHAR(500) NOT NULL COMMENT 'uploads/からの相対パス',
  `file_size` INT UNSIGNED NULL COMMENT 'ファイルサイズ（バイト）',
  `mime_type` VARCHAR(50) NULL COMMENT 'MIMEタイプ',
  `alt_text` VARCHAR(200) NULL COMMENT '代替テキスト',
  `description` TEXT NULL COMMENT '説明',
  `uploaded_by` INT UNSIGNED NOT NULL COMMENT 'アップロード者ID',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_filename` (`filename`),
  INDEX `idx_uploaded_by` (`uploaded_by`),
  FOREIGN KEY (`uploaded_by`) REFERENCES `admin_users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='画像ファイル管理';

-- ==========================================
-- 初期データ投入
-- ==========================================

-- 管理者アカウント作成（パスワード: admin123）
-- 本番環境では必ず変更すること！
INSERT INTO `admin_users` (`username`, `password`, `email`) VALUES
('admin', '$2y$10$9jkH98lYedTuO27C/6CJ..wTHyGw3OBuIB2R1STFu9IGi4UgOL4kK', 'admin@kaleidochrome.com');

-- 注意: 上記パスワードは 'admin123' です。
-- 初回ログイン後、必ず変更してください。

-- サンプル記事（オプション）
INSERT INTO `posts` (`title`, `slug`, `content`, `excerpt`, `status`, `author_id`, `published_at`) VALUES
(
  'KaleidoChromeへようこそ！',
  'welcome-to-kaleidochrome',
  '<p>KaleidoChromeの公式ブログへようこそ！</p><p>このブログでは、所属ライバーの活動情報や事務所からのお知らせなどを発信していきます。</p><p>今後とも、KaleidoChromeをよろしくお願いいたします。</p>',
  'KaleidoChromeの公式ブログがスタートしました。所属ライバーの活動情報や事務所からのお知らせを発信していきます。',
  'published',
  1,
  NOW()
);

-- ==========================================
-- 完了メッセージ
-- ==========================================
-- セットアップ完了
-- 次のステップ:
-- 1. このSQLファイルをphpMyAdminまたはMySQLクライアントで実行
-- 2. 管理者パスワードを変更
-- 3. includes/db.php に接続情報を設定

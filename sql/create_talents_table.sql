-- KaleidoChrome タレントテーブル作成（MySQL用）

CREATE TABLE IF NOT EXISTS `talents` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL COMMENT 'タレント名',
  `name_kana` VARCHAR(100) NULL COMMENT 'タレント名（かな）',
  `image_filename` VARCHAR(255) NULL COMMENT '画像ファイル名',
  `catchphrase` VARCHAR(200) NULL COMMENT 'キャッチフレーズ',
  `description` TEXT NULL COMMENT '詳細',
  `kana_tag` VARCHAR(10) NULL COMMENT 'あかさたなタグ',
  `free_tags` TEXT NULL COMMENT 'フリーワードタグ（カンマ区切り）',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_kana_tag` (`kana_tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='タレント情報';

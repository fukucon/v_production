<?php
/**
 * KaleidoChrome - 設定ファイル
 *
 * アプリケーション全体の設定を管理
 */

// エラー表示設定（開発環境）
// 本番環境では必ずコメントアウトすること
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// タイムゾーン設定
date_default_timezone_set('Asia/Tokyo');

// データベース接続情報
define('DB_HOST', 'mysql1036.onamae.ne.jp');
define('DB_NAME', 'iofy8_kaleidochrome');
define('DB_USER', 'iofy8_admin');
define('DB_PASS', 'REDACTED_PASSWORD');
define('DB_CHARSET', 'utf8mb4');

// サイト基本情報
define('SITE_NAME', 'KaleidoChrome');
define('SITE_URL', 'https://kaleidochrome.com'); // 本番環境のURL
define('SITE_DESCRIPTION', '個性が輝く無限の可能性');

// 管理画面設定
define('ADMIN_PATH', '/admin_kc'); // 管理画面のパス
define('SESSION_NAME', 'kc_admin_session');
define('SESSION_LIFETIME', 3600); // 1時間

// アップロード設定
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_URL', '/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// ページネーション設定
define('POSTS_PER_PAGE', 10); // ブログ一覧の表示件数

// セキュリティ設定
define('CSRF_TOKEN_NAME', 'csrf_token');
define('CSRF_TOKEN_LIFETIME', 3600);

// パスワード設定
define('PASSWORD_MIN_LENGTH', 8);

// デバッグモード（本番環境ではfalse）
define('DEBUG_MODE', true);

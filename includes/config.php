<?php
/**
 * KaleidoChrome - 設定ファイル
 *
 * アプリケーション全体の設定を管理
 */

// 環境変数を読み込み
require_once __DIR__ . '/env_loader.php';

// デバッグモード設定を環境変数から取得
$debugMode = filter_var($_ENV['DEBUG_MODE'] ?? 'false', FILTER_VALIDATE_BOOLEAN);
define('DEBUG_MODE', $debugMode);

// エラー表示設定（環境変数で制御）
if (DEBUG_MODE) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ALL); // ログには記録
}

// タイムゾーン設定
date_default_timezone_set('Asia/Tokyo');

// データベース接続情報（環境変数から取得）
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'kaleidochrome');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');
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

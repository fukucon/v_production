<?php
/**
 * KaleidoChrome - ローカル開発用設定ファイル
 *
 * SQLiteを使用したローカル開発環境用
 */

// エラー表示設定（開発環境）
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// タイムゾーン設定
date_default_timezone_set('Asia/Tokyo');

// データベース接続情報（SQLite）
define('DB_TYPE', 'sqlite');
define('DB_PATH', __DIR__ . '/../database/kaleidochrome.db');

// サイト基本情報
if (!defined('SITE_NAME')) define('SITE_NAME', 'KaleidoChrome');
if (!defined('SITE_URL')) define('SITE_URL', 'http://localhost:8000');
if (!defined('SITE_DESCRIPTION')) define('SITE_DESCRIPTION', '個性が輝く無限の可能性');

// 管理画面設定
if (!defined('ADMIN_PATH')) define('ADMIN_PATH', '/admin_kc');
if (!defined('SESSION_NAME')) define('SESSION_NAME', 'kc_admin_session');
if (!defined('SESSION_LIFETIME')) define('SESSION_LIFETIME', 3600);

// アップロード設定
if (!defined('UPLOAD_DIR')) define('UPLOAD_DIR', __DIR__ . '/../uploads/');
if (!defined('UPLOAD_URL')) define('UPLOAD_URL', '/uploads/');
if (!defined('MAX_FILE_SIZE')) define('MAX_FILE_SIZE', 5 * 1024 * 1024);
if (!defined('ALLOWED_EXTENSIONS')) define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// ページネーション設定
if (!defined('POSTS_PER_PAGE')) define('POSTS_PER_PAGE', 10);

// セキュリティ設定
if (!defined('CSRF_TOKEN_NAME')) define('CSRF_TOKEN_NAME', 'csrf_token');
if (!defined('CSRF_TOKEN_LIFETIME')) define('CSRF_TOKEN_LIFETIME', 3600);

// パスワード設定
if (!defined('PASSWORD_MIN_LENGTH')) define('PASSWORD_MIN_LENGTH', 8);

// デバッグモード（ローカル開発）
if (!defined('DEBUG_MODE')) define('DEBUG_MODE', true);

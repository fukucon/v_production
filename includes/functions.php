<?php
/**
 * KaleidoChrome - 共通関数
 *
 * アプリケーション全体で使用する共通関数
 */

require_once __DIR__ . '/db.php';

/**
 * セッション開始（既に開始済みの場合はスキップ）
 */
function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_name(SESSION_NAME);
        session_start();
    }
}

/**
 * ログイン済みかチェック
 *
 * @return bool
 */
function isLoggedIn() {
    startSession();
    return isset($_SESSION['admin_id']) && isset($_SESSION['admin_username']);
}

/**
 * 管理者情報取得
 *
 * @return array|null
 */
function getAdminUser() {
    if (!isLoggedIn()) {
        return null;
    }

    return [
        'id' => $_SESSION['admin_id'],
        'username' => $_SESSION['admin_username'],
        'email' => $_SESSION['admin_email'] ?? ''
    ];
}

/**
 * ログイン処理
 *
 * @param int $userId
 * @param string $username
 * @param string $email
 */
function loginAdmin($userId, $username, $email = '') {
    startSession();
    session_regenerate_id(true);
    $_SESSION['admin_id'] = $userId;
    $_SESSION['admin_username'] = $username;
    $_SESSION['admin_email'] = $email;
    $_SESSION['login_time'] = time();
}

/**
 * ログアウト処理
 */
function logoutAdmin() {
    startSession();
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}

/**
 * 管理画面へのアクセスチェック（未ログインならリダイレクト）
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . ADMIN_PATH . '/index.php?error=unauthorized');
        exit;
    }
}

/**
 * CSRFトークン生成
 *
 * @return string
 */
function generateCsrfToken() {
    startSession();
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
        $_SESSION[CSRF_TOKEN_NAME . '_time'] = time();
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * CSRFトークン検証
 *
 * @param string $token
 * @return bool
 */
function verifyCsrfToken($token) {
    startSession();

    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        return false;
    }

    // トークンの有効期限チェック
    $tokenTime = $_SESSION[CSRF_TOKEN_NAME . '_time'] ?? 0;
    if (time() - $tokenTime > CSRF_TOKEN_LIFETIME) {
        unset($_SESSION[CSRF_TOKEN_NAME]);
        unset($_SESSION[CSRF_TOKEN_NAME . '_time']);
        return false;
    }

    return hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * XSS対策のエスケープ処理
 *
 * @param string $str
 * @return string
 */
function h($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * リダイレクト
 *
 * @param string $url
 */
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

/**
 * URLスラッグ生成（日本語対応）
 *
 * @param string $str
 * @return string
 */
function generateSlug($str) {
    // 日本語をローマ字変換（簡易版）
    $str = mb_strtolower($str, 'UTF-8');
    $str = preg_replace('/[^a-z0-9\-_]/', '-', $str);
    $str = preg_replace('/-+/', '-', $str);
    $str = trim($str, '-');

    // 空の場合はランダム文字列
    if (empty($str)) {
        $str = bin2hex(random_bytes(8));
    }

    return $str;
}

/**
 * ページネーション用のオフセット計算
 *
 * @param int $page
 * @param int $perPage
 * @return int
 */
function getOffset($page, $perPage = POSTS_PER_PAGE) {
    $page = max(1, (int)$page);
    return ($page - 1) * $perPage;
}

/**
 * ページネーション用の総ページ数計算
 *
 * @param int $totalItems
 * @param int $perPage
 * @return int
 */
function getTotalPages($totalItems, $perPage = POSTS_PER_PAGE) {
    return (int)ceil($totalItems / $perPage);
}

/**
 * 日付フォーマット
 *
 * @param string $datetime
 * @param string $format
 * @return string
 */
function formatDate($datetime, $format = 'Y年m月d日') {
    if (empty($datetime)) {
        return '';
    }
    return date($format, strtotime($datetime));
}

/**
 * 文字列を指定文字数で切り詰め
 *
 * @param string $str
 * @param int $length
 * @param string $suffix
 * @return string
 */
function truncate($str, $length = 100, $suffix = '...') {
    if (mb_strlen($str, 'UTF-8') <= $length) {
        return $str;
    }
    return mb_substr($str, 0, $length, 'UTF-8') . $suffix;
}

/**
 * ファイル拡張子取得
 *
 * @param string $filename
 * @return string
 */
function getExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * 画像ファイルかチェック
 *
 * @param string $filename
 * @return bool
 */
function isImageFile($filename) {
    $ext = getExtension($filename);
    return in_array($ext, ALLOWED_EXTENSIONS);
}

/**
 * フラッシュメッセージ設定
 *
 * @param string $type (success, error, warning, info)
 * @param string $message
 */
function setFlashMessage($type, $message) {
    startSession();
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * フラッシュメッセージ取得（取得後削除）
 *
 * @return array|null
 */
function getFlashMessage() {
    startSession();
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * デバッグ出力
 *
 * @param mixed $data
 */
function debug($data) {
    if (DEBUG_MODE) {
        echo '<pre>';
        var_dump($data);
        echo '</pre>';
    }
}

/**
 * 新しいタレントコードを生成（年月＋連番形式）
 * 例: 202511001, 202511002, 202512001
 *
 * @param string $registrationDate 登録日（YYYY-MM-DD形式）
 * @return string
 */
function generateTalentCode($registrationDate) {
    // 登録日から年月を取得（YYYYMM）
    $yearMonth = date('Ym', strtotime($registrationDate));

    // その月の最大コードを取得
    $sql = "SELECT talent_code FROM talents WHERE talent_code LIKE :prefix ORDER BY talent_code DESC LIMIT 1";
    $result = db()->selectOne($sql, ['prefix' => $yearMonth . '%']);

    if ($result && !empty($result['talent_code'])) {
        // 既存のコードから連番部分を抽出して+1
        $lastCode = $result['talent_code'];
        $sequenceNumber = (int)substr($lastCode, -3);
        $newSequenceNumber = $sequenceNumber + 1;
    } else {
        // その月の最初のタレント
        $newSequenceNumber = 1;
    }

    // 新しいコードを生成（連番は3桁でゼロパディング）
    return $yearMonth . str_pad($newSequenceNumber, 3, '0', STR_PAD_LEFT);
}

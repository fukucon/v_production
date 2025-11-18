<?php
/**
 * KaleidoChrome - 管理画面ログイン
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

startSession();

// 既にログイン済みの場合はダッシュボードへ
if (isLoggedIn()) {
    redirect(ADMIN_PATH . '/dashboard.php');
}

$error = '';

// ログイン処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';

    // CSRF検証
    if (!verifyCsrfToken($csrfToken)) {
        $error = '不正なリクエストです。';
    } else {
        // ユーザー検索
        $sql = "SELECT * FROM admin_users WHERE username = :username LIMIT 1";
        $user = db()->selectOne($sql, ['username' => $username]);

        if ($user && password_verify($password, $user['password'])) {
            // ログイン成功
            loginAdmin($user['id'], $user['username'], $user['email']);

            // 最終ログイン時刻更新
            $updateSql = "UPDATE admin_users SET last_login = NOW() WHERE id = :id";
            db()->update($updateSql, ['id' => $user['id']]);

            redirect(ADMIN_PATH . '/dashboard.php');
        } else {
            $error = 'ユーザー名またはパスワードが正しくありません。';
        }
    }
}

// GETパラメータからのエラーメッセージ
if (isset($_GET['error']) && $_GET['error'] === 'unauthorized') {
    $error = 'ログインが必要です。';
}

$csrfToken = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理画面ログイン | <?php echo h(SITE_NAME); ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', 'Yu Gothic', 'Meiryo', sans-serif;
            background: #f0f0f0 !important;
            color: #000 !important;
            line-height: 1.6;
        }

        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            z-index: 10;
        }

        .login-box {
            background: rgba(255, 255, 255, 0.95);
            padding: 50px 40px;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            max-width: 450px;
            width: 100%;
            backdrop-filter: blur(10px);
        }

        .login-logo {
            text-align: center;
            margin-bottom: 40px;
        }

        .login-logo h1 {
            font-size: 32px;
            font-weight: 700;
            color: #000;
            margin-bottom: 10px;
        }

        .login-logo p {
            font-size: 14px;
            color: #666;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
            box-sizing: border-box;
            background: #fff;
        }

        .form-group input:focus {
            outline: none;
            border-color: #dc143c;
            box-shadow: 0 0 0 3px rgba(220, 20, 60, 0.1);
        }

        .login-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #dc143c, #ff1744);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(220, 20, 60, 0.4);
        }

        .error-message {
            background: rgba(255, 0, 0, 0.1);
            border-left: 4px solid #dc143c;
            color: #dc143c;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 25px;
            font-size: 14px;
        }

        .back-to-site {
            text-align: center;
            margin-top: 25px;
        }

        .back-to-site a {
            color: #666;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s ease;
        }

        .back-to-site a:hover {
            color: #dc143c;
        }

        @media (max-width: 768px) {
            .login-box {
                padding: 40px 30px;
            }

            .login-logo h1 {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-logo">
                <h1>KaleidoChrome</h1>
                <p>管理画面ログイン</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="error-message">
                    <?php echo h($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo h($csrfToken); ?>">

                <div class="form-group">
                    <label for="username">ユーザー名</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        required
                        autofocus
                        autocomplete="username"
                    >
                </div>

                <div class="form-group">
                    <label for="password">パスワード</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        autocomplete="current-password"
                    >
                </div>

                <button type="submit" class="login-btn">ログイン</button>
            </form>

            <div class="back-to-site">
                <a href="../index.html">サイトトップに戻る</a>
            </div>
        </div>
    </div>
</body>
</html>

<?php
/**
 * KaleidoChrome - パスワード変更
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

$admin = getAdminUser();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // バリデーション
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'すべてのフィールドを入力してください。';
    } elseif ($new_password !== $confirm_password) {
        $error = '新しいパスワードが一致しません。';
    } elseif (strlen($new_password) < 8) {
        $error = 'パスワードは8文字以上にしてください。';
    } else {
        // 現在のパスワード確認
        $current_admin = db()->selectOne("SELECT * FROM admin_users WHERE id = :id", ['id' => $admin['id']]);

        if (!$current_admin || !password_verify($current_password, $current_admin['password'])) {
            $error = '現在のパスワードが正しくありません。';
        } else {
            // パスワード更新
            $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $updated = db()->update(
                "UPDATE admin_users SET password = :password WHERE id = :id",
                ['password' => $new_hash, 'id' => $admin['id']]
            );

            if ($updated !== false) {
                $success = 'パスワードを変更しました。';
            } else {
                $error = 'パスワードの変更に失敗しました。';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>パスワード変更 | <?php echo SITE_NAME; ?> 管理画面</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 500px;
            width: 100%;
        }

        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }

        .user-info {
            color: #666;
            margin-bottom: 30px;
            padding: 15px;
            background: #f5f5f5;
            border-radius: 5px;
        }

        .user-info p {
            margin: 5px 0;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }

        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
        }

        .error {
            background: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #c33;
        }

        .success {
            background: #efe;
            color: #3c3;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #3c3;
        }

        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }

        button {
            flex: 1;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #ddd;
            color: #333;
        }

        .btn-secondary:hover {
            background: #ccc;
        }

        .help-text {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>パスワード変更</h1>

        <div class="user-info">
            <p><strong>ユーザー名:</strong> <?php echo h($admin['username']); ?></p>
            <p><strong>メールアドレス:</strong> <?php echo h($admin['email']); ?></p>
        </div>

        <?php if ($error): ?>
            <div class="error"><?php echo h($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success"><?php echo h($success); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="current_password">現在のパスワード</label>
                <input type="password" id="current_password" name="current_password" required>
            </div>

            <div class="form-group">
                <label for="new_password">新しいパスワード</label>
                <input type="password" id="new_password" name="new_password" required>
                <p class="help-text">8文字以上で入力してください</p>
            </div>

            <div class="form-group">
                <label for="confirm_password">新しいパスワード（確認）</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>

            <div class="button-group">
                <button type="submit" class="btn-primary">変更する</button>
                <button type="button" class="btn-secondary" onclick="location.href='dashboard.php'">戻る</button>
            </div>
        </form>
    </div>
</body>
</html>

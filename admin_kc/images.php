<?php
/**
 * KaleidoChrome - 画像管理
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

$admin = getAdminUser();
$action = $_GET['action'] ?? 'list';
$imageId = $_GET['id'] ?? null;

// 削除処理
if ($action === 'delete' && $imageId) {
    $csrfToken = $_GET['csrf_token'] ?? '';
    if (verifyCsrfToken($csrfToken)) {
        $deleted = db()->delete("DELETE FROM images WHERE id = :id", ['id' => $imageId]);
        if ($deleted) {
            setFlashMessage('success', '画像を削除しました。');
        } else {
            setFlashMessage('error', '画像の削除に失敗しました。');
        }
    }
    redirect(ADMIN_PATH . '/images.php');
}

// 保存処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'register') {
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!verifyCsrfToken($csrfToken)) {
        setFlashMessage('error', '不正なリクエストです。');
        redirect(ADMIN_PATH . '/images.php');
    }

    $filename = trim($_POST['filename'] ?? '');
    $altText = trim($_POST['alt_text'] ?? '');
    $description = trim($_POST['description'] ?? '');

    // バリデーション
    $errors = [];
    if (empty($filename)) {
        $errors[] = 'ファイル名は必須です。';
    }

    if (empty($errors)) {
        $filePath = 'uploads/' . $filename;

        $sql = "INSERT INTO images (filename, original_name, file_path, alt_text, description, uploaded_by)
                VALUES (:filename, :original_name, :file_path, :alt_text, :description, :uploaded_by)";
        $inserted = db()->insert($sql, [
            'filename' => $filename,
            'original_name' => $filename,
            'file_path' => $filePath,
            'alt_text' => $altText,
            'description' => $description,
            'uploaded_by' => $admin['id']
        ]);

        if ($inserted) {
            setFlashMessage('success', '画像を登録しました。');
            redirect(ADMIN_PATH . '/images.php');
        } else {
            $errors[] = '画像の登録に失敗しました。';
        }
    }

    if (!empty($errors)) {
        $errorMessage = implode('<br>', $errors);
        setFlashMessage('error', $errorMessage);
    }
}

// 画像一覧取得
$images = db()->select("SELECT * FROM images ORDER BY created_at DESC");

$csrfToken = generateCsrfToken();
$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>画像管理 | <?php echo h(SITE_NAME); ?> 管理画面</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', 'Yu Gothic', 'Meiryo', sans-serif; background: #f5f5f5; color: #000; line-height: 1.6; }
        .admin-wrapper { display: flex; min-height: 100vh; }
        .admin-sidebar { width: 260px; background: #ffe6f0; padding: 30px 20px; position: fixed; height: 100vh; overflow-y: auto; }
        .admin-logo { text-align: center; margin-bottom: 40px; padding-bottom: 20px; border-bottom: 2px solid rgba(0, 0, 0, 0.1); }
        .admin-logo h1 { font-size: 24px; color: #000; margin-bottom: 5px; }
        .admin-logo p { font-size: 12px; color: #666; }
        .admin-menu { list-style: none; padding: 0; margin: 0; }
        .admin-menu li { margin-bottom: 10px; }
        .admin-menu a { display: block; padding: 12px 15px; color: #000; text-decoration: none; border-radius: 8px; transition: all 0.3s ease; font-size: 14px; }
        .admin-menu a:hover, .admin-menu a.active { background: rgba(220, 20, 60, 0.1); color: #000; }
        .admin-user-info { margin-top: 40px; padding-top: 20px; border-top: 1px solid rgba(0, 0, 0, 0.1); color: #666; font-size: 13px; }
        .admin-user-info strong { color: #000; display: block; margin-bottom: 10px; }
        .admin-logout { display: inline-block; margin-top: 10px; padding: 8px 15px; background: rgba(220, 20, 60, 0.8); color: #fff; text-decoration: none; border-radius: 5px; font-size: 13px; transition: all 0.3s ease; }
        .admin-logout:hover { background: #dc143c; }
        .admin-content { margin-left: 260px; flex: 1; padding: 40px; background: #f5f5f5; min-height: 100vh; }
        .admin-header { margin-bottom: 40px; }
        .admin-header h2 { font-size: 32px; color: #333; margin-bottom: 10px; }
        .flash-message { padding: 15px 20px; border-radius: 8px; margin-bottom: 30px; font-size: 14px; }
        .flash-message.success { background: rgba(76, 175, 80, 0.1); border-left: 4px solid #4caf50; color: #2e7d32; }
        .flash-message.error { background: rgba(244, 67, 54, 0.1); border-left: 4px solid #f44336; color: #c62828; }
        .btn { display: inline-block; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 600; transition: all 0.3s ease; border: none; cursor: pointer; }
        .btn-primary { background: linear-gradient(135deg, #dc143c, #ff1744); color: white; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(220, 20, 60, 0.4); }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-danger { background: #f44336; color: white; font-size: 12px; padding: 6px 12px; }
        .image-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; }
        .image-card { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); }
        .image-card img { width: 100%; height: 200px; object-fit: cover; }
        .image-info { padding: 15px; }
        .image-filename { font-weight: 600; color: #333; margin-bottom: 5px; word-break: break-all; }
        .image-meta { font-size: 12px; color: #999; margin-bottom: 10px; }
        .image-actions { display: flex; gap: 8px; }
        .register-form { background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); max-width: 600px; margin-bottom: 40px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #333; }
        .form-group input, .form-group textarea { width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px; box-sizing: border-box; background: #fff; }
        .form-group textarea { min-height: 80px; font-family: inherit; }
        .form-group input:focus, .form-group textarea:focus { outline: none; border-color: #dc143c; }
        .info-box { background: rgba(33, 150, 243, 0.1); border-left: 4px solid #2196f3; padding: 15px; border-radius: 5px; margin-bottom: 30px; font-size: 14px; color: #1565c0; }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="admin-logo">
                <h1>KaleidoChrome</h1>
                <p>管理画面</p>
            </div>

            <ul class="admin-menu">
                <li><a href="dashboard.php">ダッシュボード</a></li>
                <li><a href="posts.php">記事管理</a></li>
                <li><a href="talents.php">タレント登録</a></li>
                <li><a href="../index.html" target="_blank">サイトを見る</a></li>
            </ul>

            <div class="admin-user-info">
                <strong><?php echo h($admin['username']); ?></strong>
                <a href="logout.php" class="admin-logout">ログアウト</a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="admin-content">
            <div class="admin-header">
                <h2>画像管理</h2>
            </div>

            <?php if ($flashMessage): ?>
                <div class="flash-message <?php echo h($flashMessage['type']); ?>">
                    <?php echo $flashMessage['message']; ?>
                </div>
            <?php endif; ?>

            <div class="info-box">
                <strong>画像の登録方法:</strong><br>
                1. FTPで画像ファイルを /uploads/ ディレクトリにアップロード<br>
                2. 下記フォームでファイル名を登録
            </div>

            <!-- 画像登録フォーム -->
            <div class="register-form">
                <h3 style="margin-bottom: 20px; color: #333;">画像を登録</h3>
                <form method="POST" action="?action=register">
                    <input type="hidden" name="csrf_token" value="<?php echo h($csrfToken); ?>">

                    <div class="form-group">
                        <label for="filename">ファイル名 *</label>
                        <input type="text" id="filename" name="filename" required placeholder="例: image.jpg">
                        <small style="color: #999; font-size: 12px;">uploads/ディレクトリにアップロード済みのファイル名を入力</small>
                    </div>

                    <div class="form-group">
                        <label for="alt_text">代替テキスト</label>
                        <input type="text" id="alt_text" name="alt_text" placeholder="画像の説明">
                    </div>

                    <div class="form-group">
                        <label for="description">説明</label>
                        <textarea id="description" name="description" placeholder="詳細な説明"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">登録</button>
                </form>
            </div>

            <!-- 画像一覧 -->
            <h3 style="margin-bottom: 20px; color: #333;">登録済み画像</h3>
            <?php if (empty($images)): ?>
                <div style="background: white; padding: 40px; text-align: center; border-radius: 12px; color: #999;">
                    画像がまだ登録されていません
                </div>
            <?php else: ?>
                <div class="image-grid">
                    <?php foreach ($images as $image): ?>
                        <div class="image-card">
                            <img src="../<?php echo h($image['file_path']); ?>" alt="<?php echo h($image['alt_text']); ?>" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'200\' height=\'200\'%3E%3Crect fill=\'%23eee\' width=\'200\' height=\'200\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' fill=\'%23999\' text-anchor=\'middle\' dy=\'.3em\'%3E画像なし%3C/text%3E%3C/svg%3E'">
                            <div class="image-info">
                                <div class="image-filename"><?php echo h($image['filename']); ?></div>
                                <div class="image-meta"><?php echo formatDate($image['created_at'], 'Y/m/d'); ?></div>
                                <div class="image-actions">
                                    <button class="btn btn-secondary" style="font-size: 12px; padding: 6px 12px;" onclick="copyFilename('<?php echo h($image['filename']); ?>')">ファイル名コピー</button>
                                    <a href="?action=delete&id=<?php echo $image['id']; ?>&csrf_token=<?php echo h($csrfToken); ?>"
                                       class="btn btn-danger"
                                       onclick="return confirm('本当に削除しますか？')">削除</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
        function copyFilename(filename) {
            navigator.clipboard.writeText(filename).then(() => {
                alert('ファイル名をコピーしました: ' + filename);
            });
        }
    </script>
</body>
</html>

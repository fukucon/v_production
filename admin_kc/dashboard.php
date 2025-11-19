<?php
/**
 * KaleidoChrome - 管理画面ダッシュボード
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

$admin = getAdminUser();

// 統計情報取得
$totalPosts = db()->selectOne("SELECT COUNT(*) as count FROM posts")['count'] ?? 0;
$publishedPosts = db()->selectOne("SELECT COUNT(*) as count FROM posts WHERE status = 'published'")['count'] ?? 0;
$draftPosts = db()->selectOne("SELECT COUNT(*) as count FROM posts WHERE status = 'draft'")['count'] ?? 0;
$totalImages = db()->selectOne("SELECT COUNT(*) as count FROM images")['count'] ?? 0;

// 最新記事5件取得
$recentPosts = db()->select(
    "SELECT id, title, status, published_at, created_at FROM posts ORDER BY created_at DESC LIMIT 5"
);

$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ダッシュボード | <?php echo h(SITE_NAME); ?> 管理画面</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', 'Yu Gothic', 'Meiryo', sans-serif; background: #f5f5f5; color: #000; line-height: 1.6; }
        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .admin-sidebar {
            width: 260px;
            background: #ffe6f0;
            padding: 30px 20px;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .admin-logo {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid rgba(0, 0, 0, 0.1);
        }

        .admin-logo h1 {
            font-size: 24px;
            color: #000;
            margin-bottom: 5px;
        }

        .admin-logo p {
            font-size: 12px;
            color: #666;
        }

        .admin-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .admin-menu li {
            margin-bottom: 10px;
        }

        .admin-menu a {
            display: block;
            padding: 12px 15px;
            color: #000;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-size: 14px;
        }

        .admin-menu a:hover,
        .admin-menu a.active {
            background: rgba(220, 20, 60, 0.1);
            color: #000;
        }

        .admin-user-info {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
            color: #666;
            font-size: 13px;
        }

        .admin-user-info strong {
            color: #000;
            display: block;
            margin-bottom: 10px;
        }

        .admin-logout {
            display: inline-block;
            margin-top: 10px;
            padding: 8px 15px;
            background: rgba(220, 20, 60, 0.8);
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            font-size: 13px;
            transition: all 0.3s ease;
        }

        .admin-logout:hover {
            background: #dc143c;
        }

        .admin-content {
            margin-left: 260px;
            flex: 1;
            padding: 40px;
            background: #f5f5f5;
            min-height: 100vh;
        }

        .admin-header {
            margin-bottom: 40px;
        }

        .admin-header h2 {
            font-size: 32px;
            color: #333;
            margin-bottom: 10px;
        }

        .admin-header p {
            color: #666;
            font-size: 14px;
        }

        .flash-message {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            font-size: 14px;
        }

        .flash-message.success {
            background: rgba(76, 175, 80, 0.1);
            border-left: 4px solid #4caf50;
            color: #2e7d32;
        }

        .flash-message.error {
            background: rgba(244, 67, 54, 0.1);
            border-left: 4px solid #f44336;
            color: #c62828;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .stat-card h3 {
            font-size: 14px;
            color: #999;
            margin-bottom: 10px;
            font-weight: 500;
        }

        .stat-card .number {
            font-size: 36px;
            font-weight: 700;
            color: #dc143c;
        }

        .recent-posts {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .recent-posts h3 {
            font-size: 20px;
            margin-bottom: 20px;
            color: #333;
        }

        .post-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .post-item {
            padding: 15px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .post-item:last-child {
            border-bottom: none;
        }

        .post-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }

        .post-meta {
            font-size: 13px;
            color: #999;
        }

        .post-status {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 12px;
            margin-left: 10px;
        }

        .post-status.published {
            background: rgba(76, 175, 80, 0.1);
            color: #4caf50;
        }

        .post-status.draft {
            background: rgba(158, 158, 158, 0.1);
            color: #757575;
        }

        @media (max-width: 768px) {
            .admin-sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .admin-content {
                margin-left: 0;
                padding: 20px;
            }

            .admin-wrapper {
                flex-direction: column;
            }
        }
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
                <li><a href="dashboard.php" class="active">ダッシュボード</a></li>
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
                <h2>ダッシュボード</h2>
                <p>ようこそ、<?php echo h($admin['username']); ?>さん</p>
            </div>

            <?php if ($flashMessage): ?>
                <div class="flash-message <?php echo h($flashMessage['type']); ?>">
                    <?php echo h($flashMessage['message']); ?>
                </div>
            <?php endif; ?>

            <!-- 統計情報 -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>総記事数</h3>
                    <div class="number"><?php echo $totalPosts; ?></div>
                </div>
                <div class="stat-card">
                    <h3>公開中</h3>
                    <div class="number"><?php echo $publishedPosts; ?></div>
                </div>
                <div class="stat-card">
                    <h3>下書き</h3>
                    <div class="number"><?php echo $draftPosts; ?></div>
                </div>
                <div class="stat-card">
                    <h3>画像</h3>
                    <div class="number"><?php echo $totalImages; ?></div>
                </div>
            </div>

            <!-- 最新記事 -->
            <div class="recent-posts">
                <h3>最新記事</h3>
                <?php if (empty($recentPosts)): ?>
                    <p style="color: #999;">まだ記事がありません。</p>
                <?php else: ?>
                    <ul class="post-list">
                        <?php foreach ($recentPosts as $post): ?>
                            <li class="post-item">
                                <div class="post-title">
                                    <?php echo h($post['title']); ?>
                                    <span class="post-status <?php echo h($post['status']); ?>">
                                        <?php echo $post['status'] === 'published' ? '公開中' : '下書き'; ?>
                                    </span>
                                </div>
                                <div class="post-meta">
                                    <?php echo formatDate($post['created_at'], 'Y/m/d H:i'); ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>

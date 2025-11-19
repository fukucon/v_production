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
$draftPosts = db()->selectOne("SELECT COUNT(*) as count FROM posts WHERE status = 'draft'")['count'] ?? 0;

// タレント別記事数
$talentPostCounts = db()->select("
    SELECT t.name, COUNT(DISTINCT pt.post_id) as count
    FROM talents t
    LEFT JOIN post_talents pt ON t.id = pt.talent_id
    LEFT JOIN posts p ON pt.post_id = p.id
    GROUP BY t.id, t.name
    ORDER BY count DESC, t.name ASC
");

// ビュー数が多い順に記事30件取得（公開済みのみ）
$topPosts = db()->select(
    "SELECT id, title, slug, published_at, view_count
     FROM posts
     WHERE status = 'published'
     ORDER BY view_count DESC, published_at DESC
     LIMIT 30"
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

        .stats-summary {
            background: white;
            padding: 15px 20px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            font-size: 13px;
            color: #666;
        }

        .stats-summary strong {
            color: #333;
            font-weight: 600;
        }

        .stats-summary .highlight {
            color: #dc143c;
            font-weight: 700;
        }

        .talent-stats {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .talent-stats h3 {
            font-size: 14px;
            color: #999;
            margin-bottom: 15px;
            font-weight: 500;
        }

        .talent-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .talent-tag {
            display: inline-block;
            padding: 6px 12px;
            background: linear-gradient(135deg, #dc143c, #ff1744);
            color: white;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }

        .talent-tag .count {
            margin-left: 4px;
            opacity: 0.9;
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
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
            transition: background 0.2s;
            cursor: pointer;
        }

        .post-item:hover {
            background: rgba(220, 20, 60, 0.03);
        }

        .post-item:last-child {
            border-bottom: none;
        }

        .post-item a {
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .post-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            font-size: 15px;
        }

        .post-meta {
            display: flex;
            gap: 20px;
            font-size: 13px;
            color: #999;
        }

        .post-date {
            color: #666;
        }

        .post-views {
            color: #dc143c;
            font-weight: 600;
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
            </div>

            <?php if ($flashMessage): ?>
                <div class="flash-message <?php echo h($flashMessage['type']); ?>">
                    <?php echo h($flashMessage['message']); ?>
                </div>
            <?php endif; ?>

            <!-- 統計情報サマリー -->
            <div class="stats-summary">
                <strong>総記事数:</strong> <span class="highlight"><?php echo $totalPosts; ?></span>件
                <strong>下書き:</strong> <span class="highlight"><?php echo $draftPosts; ?></span>件
            </div>

            <!-- タレント別記事数 -->
            <div class="talent-stats">
                <h3>タレント別記事数</h3>
                <?php if (empty($talentPostCounts)): ?>
                    <p style="color: #999; font-size: 13px;">タレントが登録されていません</p>
                <?php else: ?>
                    <div class="talent-tags">
                        <?php foreach ($talentPostCounts as $talent): ?>
                            <span class="talent-tag">
                                <?php echo h($talent['name']); ?><span class="count">: <?php echo $talent['count']; ?>件</span>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- ビュー数TOP30 -->
            <div class="recent-posts">
                <h3>ビュー数ランキング TOP30</h3>
                <?php if (empty($topPosts)): ?>
                    <p style="color: #999;">公開済みの記事がありません。</p>
                <?php else: ?>
                    <ul class="post-list">
                        <?php foreach ($topPosts as $post): ?>
                            <li class="post-item">
                                <a href="posts.php?action=edit&id=<?php echo h($post['id']); ?>">
                                    <div class="post-title"><?php echo h($post['title']); ?></div>
                                    <div class="post-meta">
                                        <span class="post-date"><?php echo formatDate($post['published_at'], 'Y/m/d'); ?></span>
                                        <span class="post-views"><?php echo number_format($post['view_count']); ?> views</span>
                                    </div>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>

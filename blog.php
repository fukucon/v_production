<?php
/**
 * KaleidoChrome - ブログ一覧
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// ページネーション
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = getOffset($page);

// 公開中かつ投稿日時が現在以前の記事のみ取得
$posts = db()->select("SELECT * FROM posts WHERE status = 'published' AND published_at <= datetime('now', 'localtime') ORDER BY published_at DESC LIMIT " . POSTS_PER_PAGE . " OFFSET " . $offset);

// 総記事数取得
$totalPosts = db()->selectOne("SELECT COUNT(*) as count FROM posts WHERE status = 'published' AND published_at <= datetime('now', 'localtime')")['count'] ?? 0;
$totalPages = getTotalPages($totalPosts);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ブログ | <?php echo h(SITE_NAME); ?></title>
    <meta name="description" content="<?php echo h(SITE_DESCRIPTION); ?> - ブログ記事一覧">
    <link rel="stylesheet" href="styles.css">
    <style>
        .blog-hero {
            min-height: auto !important;
            padding: 100px 0 20px 0 !important;
            display: flex !important;
            align-items: center !important;
            height: auto !important;
        }

        .blog-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 80px 20px;
            position: relative;
            z-index: 10;
        }

        .blog-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 30px;
            margin-bottom: 60px;
        }

        .blog-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .blog-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(220, 20, 60, 0.3);
        }

        .blog-card-image {
            width: 100%;
            aspect-ratio: 16 / 9;
            object-fit: cover;
            background: #f0f0f0;
        }

        .no-image {
            width: 100%;
            aspect-ratio: 16 / 9;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
            font-size: 18px;
            font-weight: 600;
        }

        .blog-card-content {
            padding: 20px;
            text-align: center;
        }

        .blog-card-title {
            font-size: 18px;
            font-weight: 700;
            color: #333;
            line-height: 1.4;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 60px;
        }

        .pagination a,
        .pagination span {
            display: inline-block;
            padding: 10px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .pagination a {
            background: rgba(255, 255, 255, 0.9);
            color: #333;
            border: 2px solid #e0e0e0;
        }

        .pagination a:hover {
            background: #dc143c;
            color: white;
            border-color: #dc143c;
        }

        .pagination .current {
            background: linear-gradient(135deg, #dc143c, #ff1744);
            color: white;
            border: 2px solid transparent;
        }

        .no-posts {
            text-align: center;
            padding: 80px 20px;
            color: #666;
        }

        .no-posts h3 {
            font-size: 24px;
            margin-bottom: 15px;
        }

        /* タブレット: 3列 */
        @media (max-width: 1024px) {
            .blog-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 25px;
            }
        }

        /* スマホ: 2列 */
        @media (max-width: 768px) {
            .blog-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
            }

            .blog-card-title {
                font-size: 16px;
            }

            .blog-card-content {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Animated Background -->
    <div class="animated-background">
        <?php for ($i = 0; $i < 20; $i++): ?>
            <div class="floating-bar"></div>
        <?php endfor; ?>
        <?php for ($i = 0; $i < 30; $i++): ?>
            <div class="light-beam"></div>
        <?php endfor; ?>
    </div>

    <!-- Navigation -->
    <nav class="nav">
        <div class="nav-container">
            <div class="logo">
                <a href="index.html" style="text-decoration: none;">
                    <span class="logo-text">Kaleido<span class="logo-chrome">Chrome</span></span>
                </a>
            </div>
            <ul class="nav-menu">
                <li><a href="index.html" class="nav-link">Home</a></li>
                <li><a href="talents.php" class="nav-link">Talents</a></li>
                <li><a href="liver.html" class="nav-link">Vライバーとは？</a></li>
                <li><a href="linkup.html" class="nav-link">個人配信者の方へ</a></li>
                <li><a href="blog.php" class="nav-link active">ブログ</a></li>
                <li><a href="https://forms.office.com/r/5RrHJX6MQS" target="_blank" class="nav-link">お問い合わせ</a></li>
            </ul>
            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero liver-hero blog-hero">
        <div class="container">
            <div class="hero-content">
                <h1 class="page-title">Blog</h1>
                <p class="hero-description">
                    KaleidoChromeからのお知らせや<br>
                    所属ライバーの活動情報をお届けします
                </p>
            </div>
        </div>
    </section>

    <!-- Blog List -->
    <div class="blog-container">
        <?php if (empty($posts)): ?>
            <div class="no-posts">
                <h3>まだ記事がありません</h3>
                <p>新しい記事をお楽しみに！</p>
            </div>
        <?php else: ?>
            <div class="blog-grid">
                <?php foreach ($posts as $post): ?>
                    <article class="blog-card" onclick="location.href='blog_detail.php?slug=<?php echo h($post['slug']); ?>'">
                        <?php if ($post['featured_image']): ?>
                            <img src="uploads/<?php echo h($post['featured_image']); ?>"
                                 alt="<?php echo h($post['title']); ?>"
                                 class="blog-card-image"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="no-image" style="display: none;">NO Image</div>
                        <?php else: ?>
                            <div class="no-image">NO Image</div>
                        <?php endif; ?>

                        <div class="blog-card-content">
                            <h2 class="blog-card-title"><?php echo h($post['title']); ?></h2>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>">← 前へ</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php if ($i === $page): ?>
                            <span class="current"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?>">次へ →</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <span class="logo-text">Kaleido<span class="logo-chrome">Chrome</span></span>
                    <p>個性が輝く無限の可能性</p>
                    <a href="https://x.com/kaleidochrome" target="_blank" class="footer-x-button" title="公式X">𝕏</a>
                </div>
                <div class="footer-links">
                    <a href="index.html">Home</a>
                    <a href="talents.php">Talents</a>
                    <a href="liver.html">Vライバーとは？</a>
                    <a href="linkup.html">個人配信者の方へ</a>
                    <a href="blog.php">ブログ</a>
                    <a href="https://forms.office.com/r/5RrHJX6MQS" target="_blank">お問い合わせ</a>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 KaleidoChrome. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="script.js"></script>
</body>
</html>

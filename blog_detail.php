<?php
/**
 * KaleidoChrome - ブログ詳細
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    header('HTTP/1.0 404 Not Found');
    redirect('blog.php');
}

// ローカル環境判定
$isLocal = file_exists(__DIR__ . '/database/kaleidochrome.db');
$nowFunc = $isLocal ? "datetime('now', 'localtime')" : "NOW()";

// 記事取得（公開中かつ投稿日時が現在以前）
$post = db()->selectOne("SELECT * FROM posts WHERE slug = :slug AND status = 'published' AND published_at <= {$nowFunc}", ['slug' => $slug]);

if (!$post) {
    header('HTTP/1.0 404 Not Found');
    redirect('blog.php');
}

// 閲覧数カウント
db()->update("UPDATE posts SET view_count = view_count + 1 WHERE id = :id", ['id' => $post['id']]);

// この記事に紐づいているタレントを取得
$postTalents = db()->select("
    SELECT t.id, t.name, t.slug
    FROM talents t
    INNER JOIN post_talents pt ON t.id = pt.talent_id
    WHERE pt.post_id = :post_id
    ORDER BY t.name ASC
", ['post_id' => $post['id']]);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($post['title']); ?> | <?php echo h(SITE_NAME); ?></title>
    <meta name="description" content="<?php echo h($post['excerpt'] ?: truncate(strip_tags($post['content']), 160)); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Shippori+Mincho:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <style>
        .article-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 120px 20px 80px;
            position: relative;
            z-index: 10;
        }

        .article-header {
            background: rgba(255, 255, 255, 0.4);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            padding: 25px 40px;
            border-radius: 0;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            margin-bottom: 0;
            text-align: center;
        }

        .article-title {
            font-size: 36px;
            font-weight: 700;
            color: #333;
            margin-bottom: 20px;
            line-height: 1.4;
        }

        .article-meta {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 30px;
            font-size: 14px;
            color: #999;
            flex-wrap: wrap;
        }

        .article-meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* ピクトグラムアイコン */
        .icon {
            width: 16px;
            height: 16px;
            display: inline-block;
        }

        .icon-calendar {
            background: currentColor;
            position: relative;
            border-radius: 2px;
            opacity: 0.7;
        }

        .icon-calendar::before {
            content: '';
            position: absolute;
            top: 3px;
            left: 2px;
            right: 2px;
            height: 2px;
            background: rgba(255, 255, 255, 0.95);
        }

        .icon-calendar::after {
            content: '';
            position: absolute;
            top: -3px;
            left: 3px;
            right: 3px;
            height: 2px;
            background: currentColor;
        }

        .icon-eye {
            position: relative;
            border: 2px solid currentColor;
            border-radius: 50% 50% 50% 50% / 60% 60% 40% 40%;
            opacity: 0.7;
        }

        .icon-eye::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 6px;
            height: 6px;
            background: currentColor;
            border-radius: 50%;
        }

        .article-featured-image {
            width: 100%;
            aspect-ratio: 16 / 9;
            object-fit: cover;
            border-radius: 0;
            margin: 0;
            box-shadow: none;
            display: block;
        }

        .no-image-detail {
            width: 100%;
            aspect-ratio: 16 / 9;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
            font-size: 32px;
            font-weight: 600;
            border-radius: 0;
            margin: 0;
        }

        .article-content {
            background: rgba(255, 255, 255, 0.4);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            padding: 30px 40px;
            border-radius: 0;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            margin-bottom: 40px;
        }

        .article-body {
            font-size: 16px;
            line-height: 1.9;
            color: #1a1a1a;
        }

        .article-body h2 {
            font-size: 28px;
            margin-top: 40px;
            margin-bottom: 20px;
            color: #dc143c;
            border-left: 5px solid #dc143c;
            padding-left: 15px;
        }

        .article-body h3 {
            font-size: 24px;
            margin-top: 30px;
            margin-bottom: 15px;
            color: #333;
        }

        .article-body p {
            margin-bottom: 20px;
        }

        .article-body img {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
            margin: 30px 0;
        }

        .article-body ul,
        .article-body ol {
            margin: 20px 0;
            padding-left: 30px;
        }

        .article-body li {
            margin-bottom: 10px;
        }

        .talent-tags {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #f0f0f0;
        }

        .talent-tags-label {
            font-size: 13px;
            color: #999;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .talent-tags-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .talent-tag {
            display: inline-block;
            padding: 5px 12px;
            background: linear-gradient(135deg, #dc143c, #ff1744);
            color: white;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .talent-tag:hover {
            transform: translateY(-2px);
            box-shadow: 0 3px 10px rgba(220, 20, 60, 0.3);
        }

        .article-actions {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 40px;
        }

        .btn-back {
            display: inline-block;
            padding: 15px 40px;
            background: linear-gradient(135deg, #dc143c, #ff1744);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-back:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(220, 20, 60, 0.4);
        }

        @media (max-width: 768px) {
            .article-container {
                padding: 100px 20px 60px;
            }

            .article-header {
                padding: 20px;
            }

            .article-content {
                padding: 25px 20px;
            }

            .article-featured-image,
            .no-image-detail {
                margin: 0;
            }

            .article-title {
                font-size: 28px;
            }

            .article-meta {
                gap: 15px;
                font-size: 13px;
            }

            .article-body {
                font-size: 15px;
            }

            .article-body h2 {
                font-size: 24px;
            }

            .article-body h3 {
                font-size: 20px;
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
                    <img src="images/header.webp" alt="KaleidoChrome" class="logo-image">
                </a>
            </div>
            <ul class="nav-menu">
                <li><a href="index.html" class="nav-link">HOME</a></li>
                <li><a href="talents.php" class="nav-link">TALENTS</a></li>
                <li><a href="blog.php" class="nav-link active">BLOG</a></li>
                <li><a href="liver.html" class="nav-link">Vライバーの魅力</a></li>
                <li><a href="agency.html" class="nav-link">カレイドクロームの魅力</a></li>
                <li><a href="linkup.html" class="nav-link">個人配信者の方へ</a></li>
                <li><a href="https://forms.office.com/r/5RrHJX6MQS" target="_blank" class="nav-link">お問い合わせ</a></li>
                <li class="nav-bottom-buttons mobile-only">
                    <a href="https://x.com/kaleidochrome" target="_blank" class="nav-x-btn" title="公式X">𝕏</a>
                    <a href="https://forms.office.com/r/N1cAFSeNu0" target="_blank" class="nav-apply-circle">
                        <span class="rotating-text">
                            <svg viewBox="0 0 100 100">
                                <defs>
                                    <path id="circle-path" d="M 50,50 m -38,0 a 38,38 0 1,1 76,0 a 38,38 0 1,1 -76,0"/>
                                </defs>
                                <text>
                                    <textPath href="#circle-path" textLength="238" lengthAdjust="spacing">Become a V-Liver! Become a V-Liver!</textPath>
                                </text>
                            </svg>
                        </span>
                        ライバー<br>募集中！
                    </a>
                </li>
            </ul>
            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>
    <div class="nav-overlay"></div>

    <!-- Article -->
    <article class="article-container">
        <header class="article-header">
            <h1 class="article-title"><?php echo h($post['title']); ?></h1>

            <div class="article-meta">
                <span class="article-meta-item">
                    <span class="icon icon-calendar"></span>
                    <?php echo formatDate($post['published_at'], 'Y年m月d日'); ?>
                </span>
                <span class="article-meta-item">
                    <span class="icon icon-eye"></span>
                    <?php echo number_format($post['view_count']); ?> views
                </span>
            </div>
        </header>

        <?php if ($post['featured_image']): ?>
            <img src="uploads/<?php echo h($post['featured_image']); ?>"
                 alt="<?php echo h($post['title']); ?>"
                 class="article-featured-image"
                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
            <div class="no-image-detail" style="display: none;">NO Image</div>
        <?php else: ?>
            <div class="no-image-detail">NO Image</div>
        <?php endif; ?>

        <div class="article-content">
            <div class="article-body">
                <?php echo nl2br($post['content']); // 改行を<br>に変換 & HTMLを許可 ?>
            </div>

            <?php if (!empty($postTalents)): ?>
                <div class="talent-tags">
                    <div class="talent-tags-label">関連タレント</div>
                    <div class="talent-tags-list">
                        <?php foreach ($postTalents as $talent): ?>
                            <a href="talent_detail.php?slug=<?php echo h($talent['slug']); ?>" class="talent-tag"><?php echo h($talent['name']); ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="article-actions">
            <a href="blog.php" class="btn-back">ブログ一覧に戻る</a>
        </div>
    </article>

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
                    <a href="index.html">HOME</a>
                    <a href="talents.php">TALENTS</a>
                    <a href="blog.php">BLOG</a>
                    <a href="liver.html">Vライバーの魅力</a>
                    <a href="agency.html">カレイドクロームの魅力</a>
                    <a href="linkup.html">個人配信者の方へ</a>
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

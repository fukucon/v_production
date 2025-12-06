<?php
/**
 * KaleidoChrome - タレント詳細
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$id = $_GET['id'] ?? '';
$slug = $_GET['slug'] ?? '';

// IDまたはslugが必要
if (empty($id) && empty($slug)) {
    header('HTTP/1.0 404 Not Found');
    redirect('talents.php');
}

// タレント取得（slugを優先、なければIDで検索）
if (!empty($slug)) {
    $talent = db()->selectOne("SELECT * FROM talents WHERE slug = :slug", ['slug' => $slug]);
} elseif (!empty($id) && is_numeric($id)) {
    $talent = db()->selectOne("SELECT * FROM talents WHERE id = :id", ['id' => $id]);
} else {
    $talent = false;
}

if (!$talent) {
    header('HTTP/1.0 404 Not Found');
    redirect('talents.php');
}

// このタレントに関連するブログ記事を取得
$relatedPosts = db()->select("
    SELECT p.id, p.title, p.slug, p.published_at, p.featured_image
    FROM posts p
    INNER JOIN post_talents pt ON p.id = pt.post_id
    WHERE pt.talent_id = :talent_id AND p.status = 'published'
    ORDER BY p.published_at DESC
    LIMIT 6
", ['talent_id' => $talent['id']]);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($talent['name']); ?> | <?php echo h(SITE_NAME); ?></title>
    <meta name="description" content="<?php echo h($talent['catchphrase'] ?: $talent['name'] . 'のプロフィール'); ?>">
    <link rel="stylesheet" href="styles.css">
    <style>
        .talent-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 120px 20px 80px;
            position: relative;
            z-index: 10;
        }

        .talent-main {
            background: rgba(255, 255, 255, 0.4);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            margin-bottom: 40px;
        }

        .talent-header {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 13px;
        }

        .talent-image-wrapper {
            width: 100%;
        }

        .talent-image {
            width: 100%;
            aspect-ratio: 3 / 4;
            object-fit: cover;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            transition: transform 0.05s ease-out;
            transform-style: preserve-3d;
        }

        .no-image-talent {
            width: 100%;
            aspect-ratio: 3 / 4;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
            font-size: 32px;
            font-weight: 600;
            border-radius: 15px;
            transition: transform 0.05s ease-out;
            transform-style: preserve-3d;
        }

        .talent-info {
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
        }

        .talent-name-kana {
            font-size: 14px;
            color: #999;
            margin-bottom: 2px;
        }

        .talent-name {
            font-size: 42px;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
            line-height: 1.2;
        }

        .talent-catchphrase {
            font-size: 20px;
            color: #ff69b4;
            font-weight: 600;
            margin-bottom: 5px;
            line-height: 1.4;
            word-wrap: break-word;
            word-break: break-word;
        }

        .talent-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 0;
        }

        .talent-tag {
            display: inline-block;
            padding: 6px 14px;
            background: linear-gradient(135deg, #dc143c, #ff1744);
            color: white;
            border-radius: 15px;
            font-size: 13px;
            font-weight: 600;
        }

        .talent-description {
            font-size: 16px;
            line-height: 1.7;
            color: #333;
            white-space: pre-wrap;
            margin-bottom: 15px;
        }

        .related-posts-section {
            background: rgba(255, 255, 255, 0.4);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            margin-bottom: 40px;
        }

        .related-posts-title {
            font-size: 28px;
            font-weight: 700;
            color: #333;
            margin-bottom: 30px;
            text-align: center;
        }

        .related-posts-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
        }

        .related-post-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .related-post-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(220, 20, 60, 0.2);
        }

        .related-post-image {
            width: 100%;
            aspect-ratio: 16 / 9;
            object-fit: cover;
        }

        .related-post-no-image {
            width: 100%;
            aspect-ratio: 16 / 9;
            background: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ccc;
            font-size: 14px;
            font-weight: 600;
        }

        .related-post-content {
            padding: 15px;
        }

        .related-post-title {
            font-size: 15px;
            font-weight: 600;
            color: #333;
            line-height: 1.4;
            margin-bottom: 8px;
        }

        .related-post-date {
            font-size: 12px;
            color: #999;
        }

        .no-posts-message {
            text-align: center;
            padding: 40px;
            color: #999;
            font-size: 16px;
        }

        .action-buttons {
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
            .talent-container {
                padding: 100px 20px 60px;
            }

            .talent-main {
                padding: 25px;
                display: flex;
                flex-direction: column;
            }

            .talent-header {
                display: contents;
            }

            .talent-info {
                display: contents;
            }

            .talent-image-wrapper {
                order: 1;
                max-width: 300px;
                margin: 0 auto 20px;
                width: 100%;
            }

            .talent-name-kana {
                order: 2;
                text-align: center;
            }

            .talent-name {
                order: 3;
                font-size: 32px;
                text-align: center;
            }

            .talent-catchphrase {
                order: 4;
                font-size: 18px;
                text-align: center;
                margin-bottom: 20px;
            }

            .talent-description {
                order: 5;
                padding-left: 15px;
                padding-right: 15px;
            }

            .talent-tags {
                order: 6;
                justify-content: center;
            }

            .related-posts-section {
                padding: 25px;
            }

            .related-posts-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
            }

            .related-posts-title {
                font-size: 24px;
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
                <li><a href="talents.php" class="nav-link active">TALENTS</a></li>
                <li><a href="blog.php" class="nav-link">BLOG</a></li>
                <li><a href="liver.html" class="nav-link">Vライバーとは</a></li>
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
                                    <textPath href="#circle-path">Become a V-Liver! Become a V-Liver! </textPath>
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
        <div class="nav-overlay"></div>
    </nav>

    <!-- Talent Detail -->
    <div class="talent-container">
        <div class="talent-main">
            <div class="talent-header">
                <div class="talent-image-wrapper">
                    <?php if ($talent['image_filename']): ?>
                        <img src="uploads/<?php echo h($talent['image_filename']); ?>"
                             alt="<?php echo h($talent['name']); ?>"
                             class="talent-image"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="no-image-talent" style="display: none;">NO Image</div>
                    <?php else: ?>
                        <div class="no-image-talent">NO Image</div>
                    <?php endif; ?>
                </div>

                <div class="talent-info">
                    <?php if ($talent['name_kana']): ?>
                        <div class="talent-name-kana"><?php echo h($talent['name_kana']); ?></div>
                    <?php endif; ?>

                    <h1 class="talent-name"><?php echo h($talent['name']); ?></h1>

                    <?php if ($talent['catchphrase']): ?>
                        <div class="talent-catchphrase"><?php echo h($talent['catchphrase']); ?></div>
                    <?php endif; ?>

                    <?php if ($talent['description']): ?>
                        <div class="talent-description"><?php echo h($talent['description']); ?></div>
                    <?php endif; ?>

                    <?php if ($talent['free_tags']): ?>
                        <div class="talent-tags">
                            <?php
                            $tags = array_map('trim', explode(',', $talent['free_tags']));
                            foreach ($tags as $tag):
                            ?>
                                <span class="talent-tag"><?php echo h($tag); ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Related Posts -->
        <?php if (!empty($relatedPosts)): ?>
            <div class="related-posts-section">
                <h2 class="related-posts-title">関連記事</h2>
                <div class="related-posts-grid">
                    <?php foreach ($relatedPosts as $post): ?>
                        <a href="blog_detail.php?slug=<?php echo h($post['slug']); ?>" class="related-post-card">
                            <?php if ($post['featured_image']): ?>
                                <img src="uploads/<?php echo h($post['featured_image']); ?>"
                                     alt="<?php echo h($post['title']); ?>"
                                     class="related-post-image"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="related-post-no-image" style="display: none;">NO Image</div>
                            <?php else: ?>
                                <div class="related-post-no-image">NO Image</div>
                            <?php endif; ?>
                            <div class="related-post-content">
                                <h3 class="related-post-title"><?php echo h($post['title']); ?></h3>
                                <div class="related-post-date">
                                    <?php echo formatDate($post['published_at'], 'Y年m月d日'); ?>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="action-buttons">
            <a href="talents.php" class="btn-back">タレント一覧に戻る</a>
        </div>
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
                    <a href="index.html">HOME</a>
                    <a href="talents.php">TALENTS</a>
                    <a href="blog.php">BLOG</a>
                    <a href="liver.html">Vライバーとは</a>
                    <a href="linkup.html">個人配信者の方へ</a>
                    <a href="https://forms.office.com/r/5RrHJX6MQS" target="_blank">お問い合わせ</a>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 KaleidoChrome. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // 画像の3D回転エフェクト
        (function() {
            if (window.innerWidth <= 480) return;

            function init() {
                const imageWrapper = document.querySelector('.talent-image-wrapper');
                if (!imageWrapper) return;

                if (imageWrapper.dataset.initialized === 'true') return;
                imageWrapper.dataset.initialized = 'true';

                // デバイスサイズに応じて傾き角度を調整
                const maxRotation = window.innerWidth <= 768 ? 15 : 10;

                imageWrapper.addEventListener('mousemove', function(e) {
                    const image = imageWrapper.querySelector('.talent-image') || imageWrapper.querySelector('.no-image-talent');
                    if (!image) return;

                    const rect = imageWrapper.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;

                    const centerX = rect.width / 2;
                    const centerY = rect.height / 2;

                    const rotateX = (y - centerY) / centerY * -maxRotation;
                    const rotateY = (x - centerX) / centerX * maxRotation;

                    image.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
                    image.style.willChange = 'transform';
                });

                imageWrapper.addEventListener('mouseleave', function() {
                    const image = imageWrapper.querySelector('.talent-image') || imageWrapper.querySelector('.no-image-talent');
                    if (image) {
                        image.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg)';
                        image.style.willChange = 'auto';
                    }
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', init);
            } else {
                init();
            }
        })();
    </script>
    <script src="script.js"></script>
</body>
</html>

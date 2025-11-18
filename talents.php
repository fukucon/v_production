<?php
/**
 * KaleidoChrome - タレント一覧
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// 検索パラメータ取得
$kanaFilter = $_GET['kana'] ?? '';
$tagFilter = $_GET['tag'] ?? '';

// タレント一覧取得（検索条件付き）
$sql = "SELECT * FROM talents WHERE 1=1";
$params = [];

if (!empty($kanaFilter)) {
    $sql .= " AND kana_tag = :kana";
    $params['kana'] = $kanaFilter;
}

if (!empty($tagFilter)) {
    $sql .= " AND (free_tags = :tag0 OR free_tags LIKE :tag1 OR free_tags LIKE :tag2 OR free_tags LIKE :tag3)";
    $params['tag0'] = $tagFilter; // 完全一致（単独タグ）
    $params['tag1'] = $tagFilter . ',%'; // 先頭
    $params['tag2'] = '%,' . $tagFilter . ',%'; // 中間
    $params['tag3'] = '%,' . $tagFilter; // 末尾
}

$sql .= " ORDER BY created_at ASC";
$talents = db()->select($sql, $params);

// 登録されているすべてのフリーワードタグを取得
$allTagsData = db()->select("SELECT free_tags FROM talents WHERE free_tags IS NOT NULL AND free_tags != ''");
$allTags = [];
foreach ($allTagsData as $row) {
    if (!empty($row['free_tags'])) {
        $tags = array_map('trim', explode(',', $row['free_tags']));
        $allTags = array_merge($allTags, $tags);
    }
}
$allTags = array_unique($allTags);
sort($allTags);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Talents | KaleidoChrome - カレイドクローム</title>
    <meta name="description" content="KaleidoChromeの所属タレント - 個性が輝く無限の可能性">
    <link rel="stylesheet" href="styles.css">
    <style>
        /* 検索フォーム */
        .search-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .search-form {
            display: grid;
            grid-template-columns: 1fr 1fr auto;
            gap: 15px;
            align-items: end;
        }

        .search-group {
            display: flex;
            flex-direction: column;
        }

        .search-group label {
            font-weight: 600;
            margin-bottom: 6px;
            color: #333;
            font-size: 13px;
        }

        .search-group select,
        .search-group input {
            padding: 10px 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
            background: white;
        }

        .search-group select:focus,
        .search-group input:focus {
            outline: none;
            border-color: #dc143c;
        }

        .clear-btn {
            padding: 10px 15px;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            white-space: nowrap;
        }

        .clear-btn:hover {
            background: #5a6268;
        }

        /* オートコンプリート */
        .autocomplete-container {
            position: relative;
        }

        .autocomplete-suggestions {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 2px solid #dc143c;
            border-top: none;
            border-radius: 0 0 8px 8px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            display: none;
        }

        .autocomplete-suggestions.show {
            display: block;
        }

        .autocomplete-item {
            padding: 10px 15px;
            cursor: pointer;
            transition: background 0.2s;
            color: #333;
        }

        .autocomplete-item:hover {
            background: rgba(220, 20, 60, 0.1);
        }

        @media (max-width: 768px) {
            .search-form {
                grid-template-columns: 1fr 1fr auto;
                gap: 10px;
            }

            .search-group label {
                font-size: 12px;
                margin-bottom: 4px;
            }

            .search-group select,
            .search-group input {
                padding: 8px 10px;
                font-size: 13px;
            }

            .clear-btn {
                padding: 8px 10px;
                font-size: 12px;
            }

            .search-container {
                padding: 15px;
            }
        }

        /* タレントグリッドレイアウト */
        .talents-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 30px;
            margin-bottom: 60px;
        }

        .talent-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .talent-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(220, 20, 60, 0.3);
        }

        .talent-card-image {
            width: 100%;
            aspect-ratio: 3 / 4;
            object-fit: cover;
            background: #f0f0f0;
        }

        .no-image {
            width: 100%;
            aspect-ratio: 3 / 4;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
            font-size: 18px;
            font-weight: 600;
        }

        .talent-card-content {
            padding: 20px;
            text-align: center;
        }

        .talent-card-title {
            font-size: 18px;
            font-weight: 700;
            color: #333;
            line-height: 1.4;
        }

        /* タブレット: 3列 */
        @media (max-width: 1024px) {
            .talents-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 25px;
            }
        }

        /* スマホ: 2列 */
        @media (max-width: 768px) {
            .talents-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
            }

            .talent-card-title {
                font-size: 16px;
            }

            .talent-card-content {
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
                <li><a href="talents.php" class="nav-link active">Talents</a></li>
                <li><a href="liver.html" class="nav-link">Vライバーとは？</a></li>
                <li><a href="linkup.html" class="nav-link">個人配信者の方へ</a></li>
                <li><a href="blog.php" class="nav-link">ブログ</a></li>
                <li><a href="https://forms.office.com/r/5RrHJX6MQS" target="_blank" class="nav-link">お問い合わせ</a></li>
            </ul>
            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <!-- Talents Section -->
    <section id="talents" class="talents" style="padding-top: 120px;">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Our Talents</h2>
                <div class="title-underline"></div>
            </div>

            <!-- 検索フォーム -->
            <div class="search-container">
                <form method="GET" action="talents.php" class="search-form" id="searchForm">
                    <div class="search-group">
                        <label for="kana">あかさたな検索</label>
                        <select id="kana" name="kana">
                            <option value="">すべて</option>
                            <option value="あ" <?php echo $kanaFilter === 'あ' ? 'selected' : ''; ?>>あ行</option>
                            <option value="か" <?php echo $kanaFilter === 'か' ? 'selected' : ''; ?>>か行</option>
                            <option value="さ" <?php echo $kanaFilter === 'さ' ? 'selected' : ''; ?>>さ行</option>
                            <option value="た" <?php echo $kanaFilter === 'た' ? 'selected' : ''; ?>>た行</option>
                            <option value="な" <?php echo $kanaFilter === 'な' ? 'selected' : ''; ?>>な行</option>
                            <option value="は" <?php echo $kanaFilter === 'は' ? 'selected' : ''; ?>>は行</option>
                            <option value="ま" <?php echo $kanaFilter === 'ま' ? 'selected' : ''; ?>>ま行</option>
                            <option value="や" <?php echo $kanaFilter === 'や' ? 'selected' : ''; ?>>や行</option>
                            <option value="ら" <?php echo $kanaFilter === 'ら' ? 'selected' : ''; ?>>ら行</option>
                            <option value="わ" <?php echo $kanaFilter === 'わ' ? 'selected' : ''; ?>>わ行</option>
                        </select>
                    </div>

                    <div class="search-group autocomplete-container">
                        <label for="tag">フリーワードタグ検索</label>
                        <input type="text" id="tag" name="tag" value="<?php echo h($tagFilter); ?>" placeholder="タグを入力...">
                        <div class="autocomplete-suggestions" id="autocompleteSuggestions"></div>
                    </div>

                    <a href="talents.php" class="clear-btn">クリア</a>
                </form>
            </div>

            <?php
            // 登録されているタレントの総数を取得（検索フィルタなし）
            $totalTalents = db()->selectOne("SELECT COUNT(*) as count FROM talents");
            $hasTalents = $totalTalents && $totalTalents['count'] > 0;
            ?>

            <?php if (!$hasTalents): ?>
                <div style="text-align: center; margin-top: 100px;">
                    <a href="check.html" style="font-size: 32px; font-weight: 700; color: #dc143c; text-decoration: none; transition: opacity 0.3s;">1期生募集中！</a>
                </div>
            <?php elseif (empty($talents)): ?>
                <div style="text-align: center; margin-top: 60px; padding: 40px; background: rgba(255, 255, 255, 0.95); border-radius: 15px;">
                    <p style="font-size: 18px; color: #666;">該当するタレントが見つかりませんでした</p>
                </div>
            <?php else: ?>
                <div class="talents-grid" style="margin-top: 60px;">
                    <?php foreach ($talents as $talent): ?>
                        <div class="talent-card">
                            <?php if ($talent['image_filename']): ?>
                                <img src="uploads/<?php echo h($talent['image_filename']); ?>"
                                     alt="<?php echo h($talent['name']); ?>"
                                     class="talent-card-image"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="no-image" style="display: none;">NO Image</div>
                            <?php else: ?>
                                <div class="no-image">NO Image</div>
                            <?php endif; ?>

                            <div class="talent-card-content">
                                <h3 class="talent-card-title"><?php echo h($talent['name']); ?></h3>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

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

    <!-- Floating Application Button -->
    <a href="check.html" class="floating-application-btn">
        <span>応募はこちらから</span>
    </a>

    <script>
        // 自動検索とオートコンプリート機能
        document.addEventListener('DOMContentLoaded', function() {
            const searchForm = document.getElementById('searchForm');
            const kanaSelect = document.getElementById('kana');
            const tagInput = document.getElementById('tag');
            const suggestionsBox = document.getElementById('autocompleteSuggestions');

            // 登録されているすべてのタグ
            const allTags = <?php echo json_encode($allTags); ?>;

            // あかさたな選択時に自動検索
            kanaSelect.addEventListener('change', function() {
                searchForm.submit();
            });

            // 入力イベント
            tagInput.addEventListener('input', function() {
                const inputValue = this.value.trim().toLowerCase();

                if (inputValue.length === 0) {
                    suggestionsBox.classList.remove('show');
                    suggestionsBox.innerHTML = '';
                    return;
                }

                // フィルタリング
                const filteredTags = allTags.filter(tag =>
                    tag.toLowerCase().includes(inputValue)
                );

                if (filteredTags.length === 0) {
                    suggestionsBox.classList.remove('show');
                    suggestionsBox.innerHTML = '';
                    return;
                }

                // サジェスト表示
                suggestionsBox.innerHTML = filteredTags
                    .map(tag => `<div class="autocomplete-item" data-tag="${tag}">${tag}</div>`)
                    .join('');
                suggestionsBox.classList.add('show');
            });

            // サジェストクリック時に自動検索
            suggestionsBox.addEventListener('click', function(e) {
                if (e.target.classList.contains('autocomplete-item')) {
                    tagInput.value = e.target.dataset.tag;
                    suggestionsBox.classList.remove('show');
                    suggestionsBox.innerHTML = '';
                    searchForm.submit();
                }
            });

            // 外部クリックで閉じる
            document.addEventListener('click', function(e) {
                if (!tagInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
                    suggestionsBox.classList.remove('show');
                    suggestionsBox.innerHTML = '';
                }
            });
        });
    </script>

    <script src="script.js"></script>
</body>
</html>

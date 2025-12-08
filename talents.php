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
        /* タレントヘッダーブロック */
        .talents-header-block {
            margin-bottom: 30px;
        }

        .talents-header-block .search-form {
            display: grid;
            grid-template-columns: 1fr 1fr auto;
            gap: 15px;
            align-items: end;
            margin-top: 20px;
        }

        .talents-header-block .search-group {
            display: flex;
            flex-direction: column;
        }

        .talents-header-block .search-group label {
            font-weight: 600;
            margin-bottom: 6px;
            color: #fff;
            font-size: 13px;
        }

        .talents-header-block .search-group select,
        .talents-header-block .search-group input {
            padding: 10px 12px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 0;
            font-size: 14px;
            transition: border-color 0.3s;
            background: rgba(255, 255, 255, 0.9);
            color: #333;
        }

        .talents-header-block .search-group select:focus,
        .talents-header-block .search-group input:focus {
            outline: none;
            border-color: #dc143c;
        }

        .talents-header-block .clear-btn {
            padding: 10px 15px;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 0;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            white-space: nowrap;
        }

        .talents-header-block .clear-btn:hover {
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
            .talents-header-block .search-form {
                grid-template-columns: 1fr 1fr auto;
                gap: 10px;
            }

            .talents-header-block .search-group label {
                font-size: 12px;
                margin-bottom: 4px;
            }

            .talents-header-block .search-group select,
            .talents-header-block .search-group input {
                padding: 8px 10px;
                font-size: 13px;
            }

            .talents-header-block .clear-btn {
                padding: 8px 10px;
                font-size: 12px;
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
            background: #000;
            border-radius: 0;
            overflow: hidden;
            box-shadow: 8px 8px 20px rgba(0, 0, 0, 0.8);
            position: relative;
        }

        .talent-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg,
                rgba(255, 255, 255, 0.7) 0%,
                rgba(220, 220, 230, 0.5) 30%,
                rgba(150, 150, 160, 0.2) 50%,
                transparent 75%);
            pointer-events: none;
            z-index: 0;
        }

        .talent-card > * {
            position: relative;
            z-index: 1;
        }

        .talent-card:hover {
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
            color: #fff;
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

    <!-- Talents Section -->
    <section id="talents" class="talents" style="padding-top: 120px;">
        <div class="container">
            <!-- タイトル＋検索フォーム統合ブロック -->
            <div class="about-text talents-header-block">
                <h3>Our Talents</h3>
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
                    <a href="https://forms.office.com/r/N1cAFSeNu0" target="_blank" style="font-size: 32px; font-weight: 700; color: #dc143c; text-decoration: none; transition: opacity 0.3s;">1期生募集中！</a>
                </div>
            <?php elseif (empty($talents)): ?>
                <div style="text-align: center; margin-top: 60px; padding: 40px; background: rgba(255, 255, 255, 0.95); border-radius: 15px;">
                    <p style="font-size: 18px; color: #666;">該当するタレントが見つかりませんでした</p>
                </div>
            <?php else: ?>
                <div class="talents-grid" style="margin-top: 60px;">
                    <?php foreach ($talents as $talent): ?>
                        <a href="talent_detail.php?slug=<?php echo h($talent['slug']); ?>" class="talent-card" style="text-decoration: none; color: inherit;">
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
                        </a>
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

    <!-- Floating Application Button -->
    <a href="https://forms.office.com/r/N1cAFSeNu0" target="_blank" class="floating-application-btn">
        <span class="floating-rotating-text">
            <svg viewBox="0 0 100 100">
                <defs>
                    <path id="floating-circle-path" d="M 50,50 m -38,0 a 38,38 0 1,1 76,0 a 38,38 0 1,1 -76,0"/>
                </defs>
                <text>
                    <textPath href="#floating-circle-path" textLength="238" lengthAdjust="spacing">Become a V-Liver! Become a V-Liver!</textPath>
                </text>
            </svg>
        </span>
        <span>ライバー<br>募集中！</span>
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

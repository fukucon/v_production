<?php
/**
 * KaleidoChrome - 記事管理
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

$admin = getAdminUser();
$action = $_GET['action'] ?? 'list';
$postId = $_GET['id'] ?? null;

// タレントタグ保存関数
function saveTalentTags($postId, $talentIds) {
    // 既存のタグを削除
    db()->delete("DELETE FROM post_talents WHERE post_id = :post_id", ['post_id' => $postId]);

    // 新しいタグを保存（最大50件）
    $talentIds = array_slice(array_unique($talentIds), 0, 50);
    foreach ($talentIds as $talentId) {
        if (!empty($talentId)) {
            db()->insert("INSERT INTO post_talents (post_id, talent_id) VALUES (:post_id, :talent_id)", [
                'post_id' => $postId,
                'talent_id' => $talentId
            ]);
        }
    }
}

// 削除処理
if ($action === 'delete' && $postId) {
    $csrfToken = $_GET['csrf_token'] ?? '';
    if (verifyCsrfToken($csrfToken)) {
        $deleted = db()->delete("DELETE FROM posts WHERE id = :id", ['id' => $postId]);
        if ($deleted) {
            setFlashMessage('success', '記事を削除しました。');
        } else {
            setFlashMessage('error', '記事の削除に失敗しました。');
        }
    }
    redirect(ADMIN_PATH . '/posts.php');
}

// 保存処理（新規作成・編集）
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($action, ['create', 'edit'])) {
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!verifyCsrfToken($csrfToken)) {
        setFlashMessage('error', '不正なリクエストです。');
        redirect(ADMIN_PATH . '/posts.php');
    }

    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $featuredImage = trim($_POST['featured_image'] ?? '');
    $status = $_POST['status'] ?? 'draft';
    $slug = !empty($_POST['slug']) ? trim($_POST['slug']) : generateSlug($title);
    $talentIds = $_POST['talent_ids'] ?? [];
    $publishedDatetime = trim($_POST['published_datetime'] ?? '');

    // バリデーション
    $errors = [];
    if (empty($title)) {
        $errors[] = 'タイトルは必須です。';
    }
    if (empty($content)) {
        $errors[] = '本文は必須です。';
    }
    if ($status === 'published' && empty($publishedDatetime)) {
        $errors[] = '公開時は投稿日時を指定してください。';
    }

    if (empty($errors)) {
        // 公開日時の処理
        $publishedAt = null;
        if ($status === 'published' && !empty($publishedDatetime)) {
            $publishedAt = date('Y-m-d H:i:s', strtotime($publishedDatetime));
        }

        if ($action === 'create') {
            // 新規作成
            $sql = "INSERT INTO posts (title, slug, content, featured_image, status, author_id, published_at)
                    VALUES (:title, :slug, :content, :featured_image, :status, :author_id, :published_at)";
            $inserted = db()->insert($sql, [
                'title' => $title,
                'slug' => $slug,
                'content' => $content,
                'featured_image' => $featuredImage,
                'status' => $status,
                'author_id' => $admin['id'],
                'published_at' => $publishedAt
            ]);

            if ($inserted) {
                // タレントタグを保存
                saveTalentTags($inserted, $talentIds);
                setFlashMessage('success', '記事を作成しました。');
                redirect(ADMIN_PATH . '/posts.php');
            } else {
                $errors[] = '記事の作成に失敗しました。';
            }
        } elseif ($action === 'edit' && $postId) {
            // 編集
            $sql = "UPDATE posts
                    SET title = :title, slug = :slug, content = :content,
                        featured_image = :featured_image, status = :status, published_at = :published_at
                    WHERE id = :id";
            $updated = db()->update($sql, [
                'title' => $title,
                'slug' => $slug,
                'content' => $content,
                'featured_image' => $featuredImage,
                'status' => $status,
                'published_at' => $publishedAt,
                'id' => $postId
            ]);

            if ($updated !== false) {
                // タレントタグを更新
                saveTalentTags($postId, $talentIds);
                setFlashMessage('success', '記事を更新しました。');
                redirect(ADMIN_PATH . '/posts.php');
            } else {
                $errors[] = '記事の更新に失敗しました。';
            }
        }
    }

    // エラーがあった場合
    if (!empty($errors)) {
        $errorMessage = implode('<br>', $errors);
        setFlashMessage('error', $errorMessage);
    }
}

// 編集時のデータ取得
$editPost = null;
$editPostTalentIds = [];
if ($action === 'edit' && $postId) {
    $editPost = db()->selectOne("SELECT * FROM posts WHERE id = :id", ['id' => $postId]);
    if (!$editPost) {
        setFlashMessage('error', '記事が見つかりません。');
        redirect(ADMIN_PATH . '/posts.php');
    }
    // 記事に紐づいているタレントIDを取得
    $postTalents = db()->select("SELECT talent_id FROM post_talents WHERE post_id = :post_id", ['post_id' => $postId]);
    $editPostTalentIds = array_column($postTalents, 'talent_id');
}

// 全タレント一覧取得
$allTalents = db()->select("SELECT id, name FROM talents ORDER BY name ASC");

// 記事一覧取得
$posts = db()->select("SELECT * FROM posts ORDER BY created_at DESC");

$csrfToken = generateCsrfToken();
$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>記事管理 | <?php echo h(SITE_NAME); ?> 管理画面</title>
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
        .post-status { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 12px; margin-left: 10px; }
        .post-status.published { background: rgba(76, 175, 80, 0.1); color: #4caf50; }
        .post-status.draft { background: rgba(158, 158, 158, 0.1); color: #757575; }

        .admin-actions {
            margin-bottom: 30px;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: linear-gradient(135deg, #dc143c, #ff1744);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 20, 60, 0.4);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-danger {
            background: #f44336;
            color: white;
            font-size: 12px;
            padding: 6px 12px;
        }

        .posts-table {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .posts-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .posts-table th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #000;
            border-bottom: 2px solid #e9ecef;
        }

        .posts-table td {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
            color: #000;
        }

        .posts-table tr:last-child td {
            border-bottom: none;
        }

        .post-form {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            box-sizing: border-box;
            background: #fff;
        }

        .form-group textarea {
            min-height: 300px;
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #dc143c;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 30px;
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
                <li><a href="dashboard.php">ダッシュボード</a></li>
                <li><a href="posts.php" class="active">記事管理</a></li>
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
            <?php if ($flashMessage): ?>
                <div class="flash-message <?php echo h($flashMessage['type']); ?>">
                    <?php echo $flashMessage['message']; ?>
                </div>
            <?php endif; ?>

            <?php if ($action === 'list'): ?>
                <!-- 記事一覧 -->
                <div class="admin-header">
                    <h2>記事管理</h2>
                </div>

                <div class="admin-actions">
                    <a href="?action=create" class="btn btn-primary">新規記事作成</a>
                </div>

                <div class="posts-table">
                    <table>
                        <thead>
                            <tr>
                                <th>タイトル</th>
                                <th>ステータス</th>
                                <th>投稿日</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($posts)): ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; color: #999; padding: 40px;">
                                        記事がありません
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($posts as $post): ?>
                                    <tr>
                                        <td><?php echo h($post['title']); ?></td>
                                        <td>
                                            <span class="post-status <?php echo h($post['status']); ?>">
                                                <?php echo $post['status'] === 'published' ? '公開中' : '下書き'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo $post['published_at'] ? formatDate($post['published_at'], 'Y/m/d H:i') : '-'; ?></td>
                                        <td>
                                            <a href="?action=edit&id=<?php echo $post['id']; ?>" class="btn btn-secondary" style="font-size: 12px; padding: 6px 12px;">編集</a>
                                            <a href="?action=delete&id=<?php echo $post['id']; ?>&csrf_token=<?php echo h($csrfToken); ?>"
                                               class="btn btn-danger"
                                               onclick="return confirm('本当に削除しますか？')">削除</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            <?php elseif (in_array($action, ['create', 'edit'])): ?>
                <!-- 記事作成・編集フォーム -->
                <div class="admin-header">
                    <h2><?php echo $action === 'create' ? '新規記事作成' : '記事編集'; ?></h2>
                </div>

                <div class="post-form">
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo h($csrfToken); ?>">

                        <div class="form-group">
                            <label for="title">タイトル *</label>
                            <input type="text" id="title" name="title" required value="<?php echo $editPost ? h($editPost['title']) : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label for="slug">スラッグ（URL用）</label>
                            <input type="text" id="slug" name="slug" value="<?php echo $editPost ? h($editPost['slug']) : ''; ?>" placeholder="自動生成されます">
                        </div>

                        <div class="form-group">
                            <label for="content">本文 *</label>
                            <textarea id="content" name="content" required><?php echo $editPost ? h($editPost['content']) : ''; ?></textarea>
                        </div>

                        <div class="form-group">
                            <label>タレントタグ（最大50件）</label>
                            <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                                <select id="talent_select" style="flex: 1; padding: 10px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px;">
                                    <option value="">タレントを選択...</option>
                                    <?php foreach ($allTalents as $talent): ?>
                                        <option value="<?php echo $talent['id']; ?>"><?php echo h($talent['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" id="add_talent_btn" class="btn btn-secondary" style="white-space: nowrap;">追加</button>
                            </div>
                            <div id="selected_talents" style="display: flex; flex-wrap: wrap; gap: 8px; min-height: 40px; padding: 10px; border: 2px dashed #e0e0e0; border-radius: 8px;">
                                <!-- 選択されたタレントがここに表示される -->
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="featured_image">アイキャッチ画像ファイル名</label>
                            <input type="text" id="featured_image" name="featured_image" value="<?php echo $editPost ? h($editPost['featured_image']) : ''; ?>" placeholder="例: image.jpg">
                            <small style="color: #999; font-size: 12px;">
                                画像は手動でuploads/にアップロード後、ファイル名を入力してください<br>
                                <strong style="color: #dc143c;">推奨サイズ: 1280x720px（16:9比率）</strong>
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="status">ステータス</label>
                            <select id="status" name="status">
                                <option value="draft" <?php echo ($editPost && $editPost['status'] === 'draft') ? 'selected' : ''; ?>>下書き</option>
                                <option value="published" <?php echo ($editPost && $editPost['status'] === 'published') ? 'selected' : ''; ?>>公開</option>
                            </select>
                        </div>

                        <div class="form-group" id="published-datetime-group" style="<?php echo (!$editPost || $editPost['status'] !== 'published') ? 'display: none;' : ''; ?>">
                            <label for="published_datetime">投稿日時 *</label>
                            <input type="datetime-local" id="published_datetime" name="published_datetime" value="<?php
                                if ($editPost && $editPost['published_at']) {
                                    echo date('Y-m-d\TH:i', strtotime($editPost['published_at']));
                                } else {
                                    echo date('Y-m-d\TH:i');
                                }
                            ?>">
                            <small style="color: #999; font-size: 12px;">
                                指定した日時になったら自動的に公開されます
                            </small>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">保存</button>
                            <a href="posts.php" class="btn btn-secondary">キャンセル</a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
        // タレント選択機能
        (function() {
            const talentSelect = document.getElementById('talent_select');
            const addBtn = document.getElementById('add_talent_btn');
            const selectedTalentsDiv = document.getElementById('selected_talents');
            const selectedTalents = new Map(); // talent_id => talent_name
            const MAX_TALENTS = 50;

            // 編集時の既存タレントを復元
            const editTalentIds = <?php echo json_encode($editPostTalentIds); ?>;
            if (editTalentIds.length > 0) {
                <?php foreach ($allTalents as $talent): ?>
                    if (editTalentIds.includes(<?php echo $talent['id']; ?>)) {
                        selectedTalents.set('<?php echo $talent['id']; ?>', '<?php echo addslashes(h($talent['name'])); ?>');
                    }
                <?php endforeach; ?>
                updateDisplay();
            }

            // タレント追加
            addBtn.addEventListener('click', function() {
                const selectedOption = talentSelect.options[talentSelect.selectedIndex];
                const talentId = selectedOption.value;
                const talentName = selectedOption.text;

                if (!talentId) {
                    alert('タレントを選択してください');
                    return;
                }

                if (selectedTalents.has(talentId)) {
                    alert('このタレントは既に追加されています');
                    return;
                }

                if (selectedTalents.size >= MAX_TALENTS) {
                    alert('タレントは最大50件まで追加できます');
                    return;
                }

                selectedTalents.set(talentId, talentName);
                updateDisplay();
                talentSelect.selectedIndex = 0;
            });

            // 表示を更新
            function updateDisplay() {
                selectedTalentsDiv.innerHTML = '';

                if (selectedTalents.size === 0) {
                    selectedTalentsDiv.innerHTML = '<span style="color: #999; font-size: 13px;">タレントタグなし</span>';
                } else {
                    selectedTalents.forEach((name, id) => {
                        const tag = document.createElement('span');
                        tag.style.cssText = 'display: inline-flex; align-items: center; gap: 5px; padding: 6px 12px; background: linear-gradient(135deg, #dc143c, #ff1744); color: white; border-radius: 20px; font-size: 13px; font-weight: 600;';
                        tag.innerHTML = `
                            ${escapeHtml(name)}
                            <button type="button" style="background: none; border: none; color: white; cursor: pointer; font-size: 16px; padding: 0; margin-left: 3px; line-height: 1;" data-talent-id="${id}">×</button>
                        `;
                        selectedTalentsDiv.appendChild(tag);

                        // 削除ボタンのイベント
                        tag.querySelector('button').addEventListener('click', function() {
                            selectedTalents.delete(this.dataset.talentId);
                            updateDisplay();
                        });
                    });
                }

                // hidden inputsを更新
                updateHiddenInputs();
            }

            // hidden inputsを更新
            function updateHiddenInputs() {
                // 既存のhidden inputsを削除
                document.querySelectorAll('input[name="talent_ids[]"]').forEach(input => input.remove());

                // 新しいhidden inputsを追加
                const form = document.querySelector('form');
                selectedTalents.forEach((name, id) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'talent_ids[]';
                    input.value = id;
                    form.appendChild(input);
                });
            }

            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }
        })();

        // ステータス変更時に投稿日時フィールドの表示切り替え
        document.addEventListener('DOMContentLoaded', function() {
            const statusSelect = document.getElementById('status');
            const datetimeGroup = document.getElementById('published-datetime-group');
            const datetimeInput = document.getElementById('published_datetime');

            if (statusSelect && datetimeGroup) {
                statusSelect.addEventListener('change', function() {
                    if (this.value === 'published') {
                        datetimeGroup.style.display = 'block';
                        datetimeInput.setAttribute('required', 'required');
                    } else {
                        datetimeGroup.style.display = 'none';
                        datetimeInput.removeAttribute('required');
                    }
                });
            }
        });
    </script>
</body>
</html>

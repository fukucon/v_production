<?php
/**
 * KaleidoChrome - タレント登録
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

$admin = getAdminUser();
$action = $_GET['action'] ?? 'list';
$talentId = $_GET['id'] ?? null;

// 削除処理
if ($action === 'delete' && $talentId) {
    $csrfToken = $_GET['csrf_token'] ?? '';
    if (verifyCsrfToken($csrfToken)) {
        $deleted = db()->delete("DELETE FROM talents WHERE id = :id", ['id' => $talentId]);
        if ($deleted) {
            setFlashMessage('success', 'タレントを削除しました。');
        } else {
            setFlashMessage('error', 'タレントの削除に失敗しました。');
        }
    }
    redirect(ADMIN_PATH . '/talents.php');
}

// 編集用タレントデータ取得
$editTalent = null;
if ($action === 'edit' && $talentId) {
    $editTalent = db()->selectOne("SELECT * FROM talents WHERE id = :id", ['id' => $talentId]);
    if (!$editTalent) {
        setFlashMessage('error', 'タレントが見つかりません。');
        redirect(ADMIN_PATH . '/talents.php');
    }
}

// 保存処理（新規登録）
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'register') {
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!verifyCsrfToken($csrfToken)) {
        setFlashMessage('error', '不正なリクエストです。');
        redirect(ADMIN_PATH . '/talents.php');
    }

    $name = trim($_POST['name'] ?? '');
    $registrationDate = trim($_POST['registration_date'] ?? '');
    $nameKana = trim($_POST['name_kana'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $imageFilename = trim($_POST['image_filename'] ?? '');
    $catchphrase = trim($_POST['catchphrase'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $kanaTag = trim($_POST['kana_tag'] ?? '');
    $freeTags = trim($_POST['free_tags'] ?? '');

    // バリデーション
    $errors = [];
    if (empty($name)) {
        $errors[] = 'タレント名は必須です。';
    }
    if (empty($registrationDate)) {
        $errors[] = '登録年月日は必須です。';
    }
    if (empty($slug)) {
        $errors[] = '固有URLは必須です。';
    }
    if (!preg_match('/^[a-z0-9-]+$/', $slug)) {
        $errors[] = '固有URLは半角英数字とハイフン(-)のみ使用できます。';
    }

    // スラッグの重複チェック
    $slugCheck = db()->selectOne("SELECT COUNT(*) as count FROM talents WHERE slug = :slug", ['slug' => $slug]);
    if ($slugCheck && $slugCheck['count'] > 0) {
        $errors[] = 'この固有URLは既に使用されています。';
    }

    if (empty($errors)) {
        // タレントコードを自動生成（登録年月日を元に）
        $talentCode = generateTalentCode($registrationDate);

        $sql = "INSERT INTO talents (talent_code, name, name_kana, slug, registration_date, image_filename, catchphrase, description, kana_tag, free_tags)
                VALUES (:talent_code, :name, :name_kana, :slug, :registration_date, :image_filename, :catchphrase, :description, :kana_tag, :free_tags)";
        $inserted = db()->insert($sql, [
            'talent_code' => $talentCode,
            'name' => $name,
            'name_kana' => $nameKana,
            'slug' => $slug,
            'registration_date' => $registrationDate,
            'image_filename' => $imageFilename,
            'catchphrase' => $catchphrase,
            'description' => $description,
            'kana_tag' => $kanaTag,
            'free_tags' => $freeTags
        ]);

        if ($inserted) {
            setFlashMessage('success', 'タレントを登録しました。');
            redirect(ADMIN_PATH . '/talents.php');
        } else {
            $errors[] = 'タレントの登録に失敗しました。';
        }
    }

    if (!empty($errors)) {
        $errorMessage = implode('<br>', $errors);
        setFlashMessage('error', $errorMessage);
    }
}

// 更新処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update') {
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!verifyCsrfToken($csrfToken)) {
        setFlashMessage('error', '不正なリクエストです。');
        redirect(ADMIN_PATH . '/talents.php');
    }

    $id = $_POST['id'] ?? null;
    $name = trim($_POST['name'] ?? '');
    $registrationDate = trim($_POST['registration_date'] ?? '');
    $nameKana = trim($_POST['name_kana'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $imageFilename = trim($_POST['image_filename'] ?? '');
    $catchphrase = trim($_POST['catchphrase'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $kanaTag = trim($_POST['kana_tag'] ?? '');
    $freeTags = trim($_POST['free_tags'] ?? '');

    // バリデーション
    $errors = [];
    if (empty($name)) {
        $errors[] = 'タレント名は必須です。';
    }
    if (empty($id)) {
        $errors[] = 'タレントIDが不正です。';
    }
    if (empty($registrationDate)) {
        $errors[] = '登録年月日は必須です。';
    }
    if (empty($slug)) {
        $errors[] = '固有URLは必須です。';
    }
    if (!preg_match('/^[a-z0-9-]+$/', $slug)) {
        $errors[] = '固有URLは半角英数字とハイフン(-)のみ使用できます。';
    }

    // スラッグの重複チェック（自分自身を除外）
    $slugCheck = db()->selectOne("SELECT COUNT(*) as count FROM talents WHERE slug = :slug AND id != :id",
        ['slug' => $slug, 'id' => $id]);
    if ($slugCheck && $slugCheck['count'] > 0) {
        $errors[] = 'この固有URLは既に使用されています。';
    }

    if (empty($errors)) {
        $sql = "UPDATE talents SET name = :name, name_kana = :name_kana, slug = :slug, registration_date = :registration_date,
                image_filename = :image_filename, catchphrase = :catchphrase, description = :description,
                kana_tag = :kana_tag, free_tags = :free_tags
                WHERE id = :id";
        $updated = db()->update($sql, [
            'id' => $id,
            'name' => $name,
            'name_kana' => $nameKana,
            'slug' => $slug,
            'registration_date' => $registrationDate,
            'image_filename' => $imageFilename,
            'catchphrase' => $catchphrase,
            'description' => $description,
            'kana_tag' => $kanaTag,
            'free_tags' => $freeTags
        ]);

        if ($updated !== false) {
            setFlashMessage('success', 'タレント情報を更新しました。');
            redirect(ADMIN_PATH . '/talents.php');
        } else {
            $errors[] = 'タレント情報の更新に失敗しました。';
        }
    }

    if (!empty($errors)) {
        $errorMessage = implode('<br>', $errors);
        setFlashMessage('error', $errorMessage);
    }
}

// タレント一覧取得
$talents = db()->select("SELECT * FROM talents ORDER BY created_at DESC");

$csrfToken = generateCsrfToken();
$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>タレント登録 | <?php echo h(SITE_NAME); ?> 管理画面</title>
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
        .btn-secondary { background: #6c757d; color: white; font-size: 12px; padding: 6px 12px; }
        .btn-secondary:hover { background: #5a6268; }
        .btn-danger { background: #f44336; color: white; font-size: 12px; padding: 6px 12px; }
        .talent-table { width: 100%; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); }
        .talent-table table { width: 100%; border-collapse: collapse; }
        .talent-table th { background: #ffe6f0; padding: 12px 15px; text-align: left; font-size: 13px; font-weight: 600; color: #333; border-bottom: 2px solid #ffcce0; }
        .talent-table td { padding: 12px 15px; font-size: 13px; color: #555; border-bottom: 1px solid #f5f5f5; }
        .talent-table tr:hover { background: rgba(220, 20, 60, 0.03); }
        .talent-table tr:last-child td { border-bottom: none; }
        .talent-name-cell { font-weight: 600; color: #333; }
        .talent-slug-cell { font-family: 'Courier New', monospace; color: #666; font-size: 12px; }
        .talent-actions { display: flex; gap: 8px; }
        .register-form { background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); max-width: 800px; margin-bottom: 40px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #333; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px; box-sizing: border-box; background: #fff; }
        .form-group textarea { min-height: 120px; font-family: inherit; }
        .form-group input:focus, .form-group textarea:focus, .form-group select:focus { outline: none; border-color: #dc143c; }
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
                <li><a href="talents.php" class="active">タレント登録</a></li>
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
                <h2>タレント登録</h2>
            </div>

            <?php if ($flashMessage): ?>
                <div class="flash-message <?php echo h($flashMessage['type']); ?>">
                    <?php echo $flashMessage['message']; ?>
                </div>
            <?php endif; ?>

            <div class="info-box">
                <strong>登録方法：</strong><br>
                1. タレント画像をFTPでuploads/フォルダにアップロード<br>
                2. 以下のフォームでタレント情報を登録
            </div>

            <!-- 登録・編集フォーム -->
            <div class="register-form">
                <h3 style="margin-bottom: 20px; color: #333;">
                    <?php echo $editTalent ? 'タレント編集' : '新規タレント登録'; ?>
                </h3>
                <form method="POST" action="?action=<?php echo $editTalent ? 'update' : 'register'; ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo h($csrfToken); ?>">
                    <?php if ($editTalent): ?>
                        <input type="hidden" name="id" value="<?php echo h($editTalent['id']); ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="name">タレント名 *</label>
                        <input type="text" id="name" name="name" required placeholder="例: 山田たろう"
                               value="<?php echo h($editTalent['name'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="registration_date">登録年月日 *</label>
                        <input type="date" id="registration_date" name="registration_date" required
                               value="<?php echo h($editTalent['registration_date'] ?? date('Y-m-d')); ?>">
                        <small style="color: #999; font-size: 12px;">
                            この日付の年月を元にタレントID（例: 202511001）が自動生成されます
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="name_kana">タレント名（かな）</label>
                        <input type="text" id="name_kana" name="name_kana" placeholder="例: やまだたろう"
                               value="<?php echo h($editTalent['name_kana'] ?? ''); ?>">
                        <small style="color: #999; font-size: 12px;">ひらがなで入力してください</small>
                    </div>

                    <div class="form-group">
                        <label for="slug">固有URL（スラッグ） *</label>
                        <input type="text" id="slug" name="slug" required
                               placeholder="例: yamada-taro"
                               value="<?php echo h($editTalent['slug'] ?? ''); ?>"
                               pattern="[a-z0-9-]+"
                               data-edit-id="<?php echo h($editTalent['id'] ?? ''); ?>">
                        <small style="color: #999; font-size: 12px; display: block; margin-bottom: 5px;">
                            半角英数字とハイフン(-)のみ使用可。タレントページのURL: /talent_detail.php?slug=<strong id="slug-preview">yamada-taro</strong>
                        </small>
                        <div id="slug-check-message" style="font-size: 13px; margin-top: 8px; font-weight: 600;"></div>
                    </div>

                    <div class="form-group">
                        <label for="image_filename">画像ファイル名</label>
                        <input type="text" id="image_filename" name="image_filename" placeholder="例: yamada.jpg"
                               value="<?php echo h($editTalent['image_filename'] ?? ''); ?>">
                        <small style="color: #999; font-size: 12px;">
                            画像は事前にuploads/talents/フォルダにアップロードしてください<br>
                            <strong style="color: #dc143c;">推奨サイズ: 900x1200px（3:4比率・縦長）</strong>
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="kana_tag">あかさたな登録タグ</label>
                        <select id="kana_tag" name="kana_tag">
                            <option value="">選択してください</option>
                            <option value="あ" <?php echo ($editTalent['kana_tag'] ?? '') === 'あ' ? 'selected' : ''; ?>>あ行</option>
                            <option value="か" <?php echo ($editTalent['kana_tag'] ?? '') === 'か' ? 'selected' : ''; ?>>か行</option>
                            <option value="さ" <?php echo ($editTalent['kana_tag'] ?? '') === 'さ' ? 'selected' : ''; ?>>さ行</option>
                            <option value="た" <?php echo ($editTalent['kana_tag'] ?? '') === 'た' ? 'selected' : ''; ?>>た行</option>
                            <option value="な" <?php echo ($editTalent['kana_tag'] ?? '') === 'な' ? 'selected' : ''; ?>>な行</option>
                            <option value="は" <?php echo ($editTalent['kana_tag'] ?? '') === 'は' ? 'selected' : ''; ?>>は行</option>
                            <option value="ま" <?php echo ($editTalent['kana_tag'] ?? '') === 'ま' ? 'selected' : ''; ?>>ま行</option>
                            <option value="や" <?php echo ($editTalent['kana_tag'] ?? '') === 'や' ? 'selected' : ''; ?>>や行</option>
                            <option value="ら" <?php echo ($editTalent['kana_tag'] ?? '') === 'ら' ? 'selected' : ''; ?>>ら行</option>
                            <option value="わ" <?php echo ($editTalent['kana_tag'] ?? '') === 'わ' ? 'selected' : ''; ?>>わ行</option>
                        </select>
                        <small style="color: #999; font-size: 12px;">タレント名の頭文字で選択してください</small>
                    </div>

                    <div class="form-group">
                        <label for="catchphrase">キャッチフレーズ</label>
                        <input type="text" id="catchphrase" name="catchphrase" placeholder="例: 笑顔と元気をお届けします！"
                               value="<?php echo h($editTalent['catchphrase'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="description">詳細</label>
                        <textarea id="description" name="description" placeholder="タレントの詳細情報や自己紹介など..."><?php echo h($editTalent['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="free_tags">フリーワードタグ</label>
                        <input type="text" id="free_tags" name="free_tags" placeholder="例: ゲーム配信, 歌枠, ASMR"
                               value="<?php echo h($editTalent['free_tags'] ?? ''); ?>">
                        <small style="color: #999; font-size: 12px;">カンマ区切りで複数入力可能</small>
                    </div>

                    <div style="display: flex; gap: 10px;">
                        <button type="submit" class="btn btn-primary">
                            <?php echo $editTalent ? '更新' : '登録'; ?>
                        </button>
                        <?php if ($editTalent): ?>
                            <a href="talents.php" class="btn btn-secondary">キャンセル</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- タレント一覧 -->
            <h3 style="margin-bottom: 20px; color: #333; font-size: 24px;">登録済みタレント</h3>

            <?php if (empty($talents)): ?>
                <div style="text-align: center; padding: 60px; background: white; border-radius: 12px; color: #999;">
                    <p>まだタレントが登録されていません</p>
                </div>
            <?php else: ?>
                <div class="talent-table">
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 150px;">タレントID</th>
                                <th>タレント名</th>
                                <th style="width: 250px;">かな</th>
                                <th style="width: 160px;">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($talents as $talent): ?>
                                <tr>
                                    <td style="font-family: 'Courier New', monospace; font-weight: 600; color: #dc143c;">
                                        <?php echo h($talent['talent_code'] ?: 'ID:' . $talent['id']); ?>
                                    </td>
                                    <td class="talent-name-cell"><?php echo h($talent['name']); ?></td>
                                    <td><?php echo h($talent['name_kana'] ?: '-'); ?></td>
                                    <td>
                                        <div class="talent-actions">
                                            <a href="?action=edit&id=<?php echo $talent['id']; ?>"
                                               class="btn btn-secondary">編集</a>
                                            <a href="?action=delete&id=<?php echo $talent['id']; ?>&csrf_token=<?php echo h($csrfToken); ?>"
                                               class="btn btn-danger"
                                               onclick="return confirm('本当に削除しますか？')">削除</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
        // スラッグのリアルタイム重複チェック
        (function() {
            const slugInput = document.getElementById('slug');
            const slugPreview = document.getElementById('slug-preview');
            const checkMessage = document.getElementById('slug-check-message');
            let checkTimeout = null;

            if (!slugInput) return;

            const editId = slugInput.getAttribute('data-edit-id');

            slugInput.addEventListener('input', function() {
                const slug = this.value.trim();

                // プレビュー更新
                if (slugPreview) {
                    slugPreview.textContent = slug || 'yamada-taro';
                }

                // 空の場合はチェックしない
                if (slug.length === 0) {
                    checkMessage.textContent = '';
                    checkMessage.style.color = '';
                    return;
                }

                // 形式チェック
                if (!/^[a-z0-9-]+$/.test(slug)) {
                    checkMessage.textContent = '半角英数字とハイフン(-)のみ使用できます';
                    checkMessage.style.color = '#f44336';
                    return;
                }

                // デバウンス処理（入力が止まって500ms後にチェック）
                clearTimeout(checkTimeout);
                checkMessage.textContent = 'チェック中...';
                checkMessage.style.color = '#999';

                checkTimeout = setTimeout(function() {
                    // AJAX重複チェック
                    const url = 'check_slug.php?slug=' + encodeURIComponent(slug) +
                        (editId ? '&exclude_id=' + encodeURIComponent(editId) : '');

                    fetch(url)
                        .then(response => response.json())
                        .then(data => {
                            if (data.available) {
                                checkMessage.textContent = '✓ ' + data.message;
                                checkMessage.style.color = '#4caf50'; // 緑色
                            } else {
                                checkMessage.textContent = '✗ ' + data.message;
                                checkMessage.style.color = '#f44336'; // 赤色
                            }
                        })
                        .catch(error => {
                            checkMessage.textContent = 'チェックエラー';
                            checkMessage.style.color = '#f44336';
                            console.error('Slug check error:', error);
                        });
                }, 500);
            });

            // 初期値がある場合は即座にチェック
            if (slugInput.value.trim().length > 0) {
                slugInput.dispatchEvent(new Event('input'));
            }
        })();
    </script>
</body>
</html>

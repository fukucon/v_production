<?php
/**
 * タレントスラッグ重複チェックAPI
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');

// ログイン不要（管理画面内からのAJAXリクエストのため）
// ただし、セキュリティ上、簡易的なチェックを追加することも可能

$slug = $_GET['slug'] ?? '';
$excludeId = $_GET['exclude_id'] ?? null;

if (empty($slug)) {
    echo json_encode(['available' => false, 'message' => 'スラッグを入力してください']);
    exit;
}

// スラッグの形式チェック（英数字とハイフンのみ）
if (!preg_match('/^[a-z0-9-]+$/', $slug)) {
    echo json_encode(['available' => false, 'message' => '半角英数字とハイフン(-)のみ使用できます']);
    exit;
}

// 重複チェック
$sql = "SELECT COUNT(*) as count FROM talents WHERE slug = :slug";
$params = ['slug' => $slug];

// 編集時は自分自身を除外
if ($excludeId) {
    $sql .= " AND id != :exclude_id";
    $params['exclude_id'] = $excludeId;
}

$result = db()->selectOne($sql, $params);
$exists = $result && $result['count'] > 0;

if ($exists) {
    echo json_encode(['available' => false, 'message' => 'このアドレスは既に使われています']);
} else {
    echo json_encode(['available' => true, 'message' => 'このアドレスは使用できます']);
}

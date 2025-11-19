<?php
/**
 * マイグレーション: talentsテーブルにslugカラムを追加
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

try {
    $db = db();
    $pdo = $db->getConnection();

    // slugカラムを追加
    echo "talentsテーブルにslugカラムを追加中...\n";
    $pdo->exec("ALTER TABLE talents ADD COLUMN slug TEXT");

    // 既存のタレントに対してIDベースのスラッグを設定
    echo "既存タレントにデフォルトslugを設定中...\n";
    $pdo->exec("UPDATE talents SET slug = 'talent-' || id WHERE slug IS NULL OR slug = ''");

    // ユニークインデックスを作成
    echo "ユニークインデックスを作成中...\n";
    $pdo->exec("CREATE UNIQUE INDEX IF NOT EXISTS idx_talent_slug ON talents(slug)");

    echo "✅ マイグレーション完了！\n";

} catch (Exception $e) {
    echo "❌ エラー: " . $e->getMessage() . "\n";
}

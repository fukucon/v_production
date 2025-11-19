<?php
/**
 * マイグレーション: talentsテーブルにtalent_codeカラムを追加
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

try {
    $db = db();
    $pdo = $db->getConnection();

    // talent_codeカラムを追加
    echo "talentsテーブルにtalent_codeカラムを追加中...\n";
    $pdo->exec("ALTER TABLE talents ADD COLUMN talent_code TEXT");

    // ユニークインデックスを作成
    echo "ユニークインデックスを作成中...\n";
    $pdo->exec("CREATE UNIQUE INDEX IF NOT EXISTS idx_talent_code ON talents(talent_code)");

    echo "✅ マイグレーション完了！\n";

} catch (Exception $e) {
    echo "❌ エラー: " . $e->getMessage() . "\n";
}

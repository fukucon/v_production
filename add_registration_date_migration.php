<?php
/**
 * マイグレーション: talentsテーブルにregistration_dateカラムを追加
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

try {
    $db = db();
    $pdo = $db->getConnection();

    // registration_dateカラムを追加
    echo "talentsテーブルにregistration_dateカラムを追加中...\n";
    $pdo->exec("ALTER TABLE talents ADD COLUMN registration_date DATE");

    // 既存のタレントにはcreated_atから日付を設定
    echo "既存タレントに登録日を設定中...\n";
    $pdo->exec("UPDATE talents SET registration_date = DATE(created_at) WHERE registration_date IS NULL");

    echo "✅ マイグレーション完了！\n";

} catch (Exception $e) {
    echo "❌ エラー: " . $e->getMessage() . "\n";
}

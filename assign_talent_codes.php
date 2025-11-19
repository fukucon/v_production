<?php
/**
 * 既存タレントにtalent_codeを付与するスクリプト
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

try {
    $db = db();

    // talent_codeがNULLまたは空のタレントを取得
    $talents = $db->select("SELECT * FROM talents WHERE talent_code IS NULL OR talent_code = '' ORDER BY created_at ASC");

    if (empty($talents)) {
        echo "✅ すべてのタレントに既にコードが付与されています。\n";
        exit;
    }

    echo "既存タレント " . count($talents) . " 件にコードを付与します...\n\n";

    // 月ごとの連番を管理
    $monthCounters = [];

    foreach ($talents as $talent) {
        // 作成日から年月を取得
        $createdAt = $talent['created_at'];
        $yearMonth = date('Ym', strtotime($createdAt));

        // その月の連番を取得・インクリメント
        if (!isset($monthCounters[$yearMonth])) {
            $monthCounters[$yearMonth] = 1;
        } else {
            $monthCounters[$yearMonth]++;
        }

        // タレントコードを生成
        $talentCode = $yearMonth . str_pad($monthCounters[$yearMonth], 3, '0', STR_PAD_LEFT);

        // データベースを更新
        $db->update(
            "UPDATE talents SET talent_code = :talent_code WHERE id = :id",
            ['talent_code' => $talentCode, 'id' => $talent['id']]
        );

        echo "ID {$talent['id']} ({$talent['name']}): {$talentCode} を付与\n";
    }

    echo "\n✅ 完了しました！\n";

} catch (Exception $e) {
    echo "❌ エラー: " . $e->getMessage() . "\n";
}

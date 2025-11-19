<?php
/**
 * KaleidoChrome - ローカル開発環境セットアップスクリプト
 *
 * SQLiteデータベースを初期化します
 */

$dbPath = __DIR__ . '/database/kaleidochrome.db';
$sqlPath = __DIR__ . '/sql/setup_sqlite.sql';

echo "=================================\n";
echo "KaleidoChrome ローカル環境セットアップ\n";
echo "=================================\n\n";

// データベースディレクトリの確認
if (!is_dir(__DIR__ . '/database')) {
    mkdir(__DIR__ . '/database', 0755, true);
    echo "✓ databaseディレクトリを作成しました\n";
}

// 既存のデータベースがあれば削除
if (file_exists($dbPath)) {
    unlink($dbPath);
    echo "✓ 既存のデータベースを削除しました\n";
}

// SQLiteデータベースを作成
try {
    $pdo = new PDO("sqlite:" . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ SQLiteデータベースを作成しました\n";

    // SQLファイルを読み込んで実行
    if (!file_exists($sqlPath)) {
        throw new Exception("SQLファイルが見つかりません: " . $sqlPath);
    }

    $sql = file_get_contents($sqlPath);
    $pdo->exec($sql);
    echo "✓ テーブルを作成しました\n";
    echo "✓ 初期データを投入しました\n";

    // パーミッション設定
    chmod($dbPath, 0666);
    echo "✓ データベースのパーミッションを設定しました\n";

    echo "\n=================================\n";
    echo "セットアップ完了！\n";
    echo "=================================\n\n";

    echo "管理者アカウント:\n";
    echo "  ユーザー名: admin\n";
    echo "  パスワード: admin123\n\n";

    echo "サーバーを起動してください:\n";
    echo "  php -S localhost:8000\n\n";

    echo "ブラウザでアクセス:\n";
    echo "  トップページ: http://localhost:8000/index.html\n";
    echo "  ブログ: http://localhost:8000/blog.php\n";
    echo "  管理画面: http://localhost:8000/admin_kc/\n\n";

} catch (Exception $e) {
    echo "\n✗ エラーが発生しました:\n";
    echo $e->getMessage() . "\n\n";
    exit(1);
}

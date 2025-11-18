<?php
/**
 * KaleidoChrome - データベース接続
 *
 * PDOを使用した安全なデータベース接続
 * ローカル環境（SQLite）と本番環境（MySQL）を自動切り替え
 */

// ローカル環境判定（SQLiteファイルが存在する場合はローカル）
$isLocal = file_exists(__DIR__ . '/../database/kaleidochrome.db');

if ($isLocal) {
    require_once __DIR__ . '/config_local.php';
} else {
    require_once __DIR__ . '/config.php';
}

class Database {
    private static $instance = null;
    private $pdo;

    /**
     * コンストラクタ（プライベート：シングルトンパターン）
     */
    private function __construct() {
        try {
            // ローカル環境判定
            $isLocal = file_exists(__DIR__ . '/../database/kaleidochrome.db');

            if ($isLocal) {
                // SQLite（ローカル開発環境）
                $dsn = "sqlite:" . DB_PATH;
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ];
                $this->pdo = new PDO($dsn, null, null, $options);
            } else {
                // MySQL（本番環境）
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
                ];
                $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            }

            if (DEBUG_MODE) {
                $dbType = $isLocal ? 'SQLite' : 'MySQL';
                error_log("[DB] {$dbType}データベース接続成功");
            }
        } catch (PDOException $e) {
            error_log("[DB ERROR] " . $e->getMessage());
            die("データベース接続エラーが発生しました。");
        }
    }

    /**
     * インスタンス取得（シングルトン）
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * PDOオブジェクト取得
     */
    public function getConnection() {
        return $this->pdo;
    }

    /**
     * SELECT文実行
     *
     * @param string $sql SQL文
     * @param array $params パラメータ
     * @return array 結果配列
     */
    public function select($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("[DB SELECT ERROR] " . $e->getMessage());
            return [];
        }
    }

    /**
     * SELECT文実行（1行取得）
     *
     * @param string $sql SQL文
     * @param array $params パラメータ
     * @return array|false 結果配列またはfalse
     */
    public function selectOne($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("[DB SELECT ONE ERROR] " . $e->getMessage());
            return false;
        }
    }

    /**
     * INSERT文実行
     *
     * @param string $sql SQL文
     * @param array $params パラメータ
     * @return int|false 挿入されたID、失敗時はfalse
     */
    public function insert($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("[DB INSERT ERROR] " . $e->getMessage());
            return false;
        }
    }

    /**
     * UPDATE文実行
     *
     * @param string $sql SQL文
     * @param array $params パラメータ
     * @return int|false 影響を受けた行数、失敗時はfalse
     */
    public function update($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("[DB UPDATE ERROR] " . $e->getMessage());
            return false;
        }
    }

    /**
     * DELETE文実行
     *
     * @param string $sql SQL文
     * @param array $params パラメータ
     * @return int|false 削除された行数、失敗時はfalse
     */
    public function delete($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("[DB DELETE ERROR] " . $e->getMessage());
            return false;
        }
    }

    /**
     * トランザクション開始
     */
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }

    /**
     * コミット
     */
    public function commit() {
        return $this->pdo->commit();
    }

    /**
     * ロールバック
     */
    public function rollback() {
        return $this->pdo->rollBack();
    }

    /**
     * クローン禁止
     */
    private function __clone() {}

    /**
     * アンシリアライズ禁止
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

/**
 * データベースインスタンス取得のヘルパー関数
 *
 * @return Database
 */
function db() {
    return Database::getInstance();
}

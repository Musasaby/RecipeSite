<?php
/**
 * データベース接続を管理するクラス
 */
class DatabaseConnection {
    private static $instance = null;
    private $pdo;
    
    /**
     * コンストラクタ - データベース接続を確立
     */
    private function __construct() {
        try {
            $dsn = 'mysql:host=' . Config::$dbHost . ';dbname=' . Config::$dbName;
            $this->pdo = new PDO($dsn, Config::$dbUser, Config::$dbPassword);
            
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new Exception("データベース接続エラー: " . $e->getMessage());
        }
    }
    
    /**
     * シングルトンパターン - インスタンスを取得
     * @return DatabaseConnection
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * PDOインスタンスを取得
     * @return PDO
     */
    public function getConnection() {
        return $this->pdo;
    }
}
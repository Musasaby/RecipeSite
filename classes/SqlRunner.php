<?php
/**
 * SQLファイルを実行するクラス
 */
class SqlRunner {
    private $db;
    
    /**
     * コンストラクタ
     * @param PDO $db データベース接続
     */
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * SQLファイルを実行
     * @param string $filePath SQLファイルのパス
     * @return array 実行結果 ['success' => bool, 'message' => string]
     */
    public function executeSqlFile($filePath) {
        if (!file_exists($filePath)) {
            return ['success' => false, 'message' => "SQLファイルが見つかりません: {$filePath}"];
        }
        
        try {
            $sql = file_get_contents($filePath);
            
            // SQLクエリを実行
            $this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, 0);
            $this->db->exec($sql);
            
            return ['success' => true, 'message' => "SQLファイルを正常に実行しました"];
            
        } catch (PDOException $e) {
            return ['success' => false, 'message' => "SQL実行エラー: " . $e->getMessage()];
        }
    }
}
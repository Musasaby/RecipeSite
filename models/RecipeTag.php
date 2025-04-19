<?php
require_once 'Database.php';

class RecipeTag {
    // データベース接続とテーブル名
    private $conn;
    private $table = 'RecipeTag';

    // プロパティ
    public $recipe_id;
    public $tag_id;

    // コンストラクタ
    public function __construct($db = null) {
        if ($db) {
            $this->conn = $db;
        } else {
            $database = new Database();
            $this->conn = $database->connect();
        }
    }

    // レシピにタグを追加
    public function addTagToRecipe() {
        // 重複チェック
        $check_query = 'SELECT * FROM ' . $this->table . ' WHERE recipe_id = ? AND tag_id = ?';
        $check_stmt = $this->conn->prepare($check_query);
        $check_stmt->bindParam(1, $this->recipe_id);
        $check_stmt->bindParam(2, $this->tag_id);
        $check_stmt->execute();
        
        if($check_stmt->rowCount() > 0) {
            // 既に関連付けられている
            return true;
        }
        
        // 新しい関連付けを作成
        $query = 'INSERT INTO ' . $this->table . ' SET recipe_id = :recipe_id, tag_id = :tag_id';
        $stmt = $this->conn->prepare($query);
        
        // パラメータのクリーニング
        $this->recipe_id = htmlspecialchars(strip_tags($this->recipe_id));
        $this->tag_id = htmlspecialchars(strip_tags($this->tag_id));
        
        // パラメータをバインド
        $stmt->bindParam(':recipe_id', $this->recipe_id);
        $stmt->bindParam(':tag_id', $this->tag_id);
        
        // クエリ実行
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    // レシピに関連付けられたタグを取得
    public function getTagsByRecipe($recipe_id = null) {
        if($recipe_id) {
            $this->recipe_id = $recipe_id;
        }
        
        $query = 'SELECT 
                    t.tag_id,
                    t.tag_name
                FROM 
                    Tags t
                INNER JOIN 
                    ' . $this->table . ' rt ON t.tag_id = rt.tag_id
                WHERE 
                    rt.recipe_id = ?
                ORDER BY 
                    t.tag_name ASC';
        
        // ステートメント準備
        $stmt = $this->conn->prepare($query);
        
        // IDをバインド
        $stmt->bindParam(1, $this->recipe_id);
        
        // クエリ実行
        $stmt->execute();
        
        return $stmt;
    }
    
    // レシピに関連付けられたすべてのタグを削除
    public function removeAllTagsFromRecipe($recipe_id = null) {
        if($recipe_id) {
            $this->recipe_id = $recipe_id;
        }
        
        $query = 'DELETE FROM ' . $this->table . ' WHERE recipe_id = ?';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->recipe_id);
        
        return $stmt->execute();
    }
    
    // タグを削除
    public function removeTagFromRecipe() {
        $query = 'DELETE FROM ' . $this->table . ' WHERE recipe_id = ? AND tag_id = ?';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->recipe_id);
        $stmt->bindParam(2, $this->tag_id);
        
        return $stmt->execute();
    }
    
    // タグを使用しているレシピ数をカウント
    public function countRecipesByTagId($tag_id = null) {
        if($tag_id) {
            $this->tag_id = $tag_id;
        }
        
        $query = 'SELECT COUNT(*) as count FROM ' . $this->table . ' WHERE tag_id = ?';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->tag_id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$row['count'];
    }
}
?>
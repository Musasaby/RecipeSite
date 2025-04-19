<?php
require_once 'Database.php';

class Tag {
    // データベース接続とテーブル名
    private $conn;
    private $table = 'Tags';

    // プロパティ
    public $tag_id;
    public $tag_name;

    // コンストラクタ
    public function __construct($db = null) {
        if ($db) {
            $this->conn = $db;
        } else {
            $database = new Database();
            $this->conn = $database->connect();
        }
    }

    // タグ詳細の取得
    public function getTagDetails($id = null) {
        if ($id) {
            $this->tag_id = $id;
        }
        
        $query = 'SELECT
                    t.tag_id,
                    t.tag_name
                FROM
                    ' . $this->table . ' t
                WHERE
                    t.tag_id = ?';
        
        // ステートメント準備
        $stmt = $this->conn->prepare($query);
        
        // IDをバインド
        $stmt->bindParam(1, $this->tag_id);
        
        // クエリ実行
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->tag_name = $row['tag_name'];
            return true;
        }
        
        return false;
    }

    // タグ作成（存在しない場合）
    public function createTag() {
        // タグ名が空でないか確認
        if(empty($this->tag_name)) {
            return false;
        }
        
        // タグが既に存在するか確認
        $query = 'SELECT tag_id FROM ' . $this->table . ' WHERE tag_name = ?';
        $stmt = $this->conn->prepare($query);
        $this->tag_name = htmlspecialchars(strip_tags($this->tag_name));
        $stmt->bindParam(1, $this->tag_name);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            // 既存のタグIDを返す
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->tag_id = $row['tag_id'];
            return true;
        }
        
        // 新しいタグを作成
        $query = 'INSERT INTO ' . $this->table . ' SET tag_name = :tag_name';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':tag_name', $this->tag_name);
        
        if($stmt->execute()) {
            $this->tag_id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }

    // タグに関連するレシピを取得
    public function getRecipesByTag() {
        $query = 'SELECT 
                    r.recipe_id,
                    r.recipe_name,
                    r.cook_time,
                    r.material as ingredients,
                    r.explanation as description,
                    r.steps,
                    r.created_at,
                    r.updated_at
                FROM 
                    Recipes r
                INNER JOIN 
                    RecipeTag rt ON r.recipe_id = rt.recipe_id
                WHERE 
                    rt.tag_id = ?
                ORDER BY 
                    r.created_at DESC';
        
        // ステートメント準備
        $stmt = $this->conn->prepare($query);
        
        // IDをバインド
        $stmt->bindParam(1, $this->tag_id);
        
        // クエリ実行
        $stmt->execute();
        
        return $stmt;
    }
    
    // すべてのタグを取得
    public function getAllTags() {
        $query = 'SELECT tag_id, tag_name FROM ' . $this->table . ' ORDER BY tag_name ASC';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    
    // タグを削除
    public function delete() {
        // クエリを作成
        $query = 'DELETE FROM ' . $this->table . ' WHERE tag_id = :tag_id';
        
        // ステートメントを準備
        $stmt = $this->conn->prepare($query);
        
        // データクリーニング
        $this->tag_id = htmlspecialchars(strip_tags($this->tag_id));
        
        // パラメータをバインド
        $stmt->bindParam(':tag_id', $this->tag_id);
        
        // クエリを実行
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // タグを更新
    public function update() {
        // クエリを作成
        $query = 'UPDATE ' . $this->table . ' SET tag_name = :tag_name WHERE tag_id = :tag_id';
        
        // ステートメントを準備
        $stmt = $this->conn->prepare($query);
        
        // データクリーニング
        $this->tag_name = htmlspecialchars(strip_tags($this->tag_name));
        $this->tag_id = htmlspecialchars(strip_tags($this->tag_id));
        
        // パラメータをバインド
        $stmt->bindParam(':tag_name', $this->tag_name);
        $stmt->bindParam(':tag_id', $this->tag_id);
        
        // クエリを実行
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // 同じ名前のタグが存在するか確認（自分自身は除く）
    public function existsWithSameNameExcludingSelf() {
        // クエリを作成
        $query = 'SELECT COUNT(*) FROM ' . $this->table . ' WHERE tag_name = :tag_name AND tag_id != :tag_id';
        
        // ステートメントを準備
        $stmt = $this->conn->prepare($query);
        
        // データクリーニング
        $this->tag_name = htmlspecialchars(strip_tags($this->tag_name));
        $this->tag_id = htmlspecialchars(strip_tags($this->tag_id));
        
        // パラメータをバインド
        $stmt->bindParam(':tag_name', $this->tag_name);
        $stmt->bindParam(':tag_id', $this->tag_id);
        
        // クエリを実行
        $stmt->execute();
        
        // 結果を取得
        return (int)$stmt->fetchColumn() > 0;
    }
}
?>
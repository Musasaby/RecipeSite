<?php
require_once 'Database.php';

class Recipe {
    // データベース接続とテーブル名
    private $conn;
    private $table = 'Recipes';

    // プロパティ
    public $recipe_id;
    public $recipe_name;
    public $description;
    public $cook_time;
    public $steps;
    public $ingredients;
    public $created_at;
    public $updated_at;

    // コンストラクタ
    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    // レシピの詳細を取得
    public function getRecipeDetails($id = null) {
        if ($id) {
            $this->recipe_id = $id;
        }
        
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
                    ' . $this->table . ' r
                WHERE
                    r.recipe_id = ?';
        
        // ステートメント準備
        $stmt = $this->conn->prepare($query);
        
        // IDをバインド
        $stmt->bindParam(1, $this->recipe_id);
        
        // クエリ実行
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->recipe_name = $row['recipe_name'];
            $this->cook_time = $row['cook_time'];
            $this->ingredients = $row['ingredients'];
            $this->description = $row['description'];
            $this->steps = $row['steps'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            
            return true;
        }
        
        return false;
    }

    // レシピ作成
    public function createRecipe() {
        // 現在の日時
        $this->created_at = date('Y-m-d H:i:s');
        $this->updated_at = $this->created_at;
        
        // クエリ
        $query = 'INSERT INTO ' . $this->table . '
                SET
                    recipe_name = :recipe_name,
                    cook_time = :cook_time,
                    material = :ingredients,
                    explanation = :description,
                    steps = :steps,
                    created_at = :created_at,
                    updated_at = :updated_at';

        // ステートメント準備
        $stmt = $this->conn->prepare($query);
        
        // パラメータのクリーニング
        $this->recipe_name = htmlspecialchars(strip_tags($this->recipe_name));
        $this->cook_time = htmlspecialchars(strip_tags($this->cook_time));
        $this->ingredients = htmlspecialchars(strip_tags($this->ingredients));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->steps = htmlspecialchars(strip_tags($this->steps));
        
        // パラメータをバインド
        $stmt->bindParam(':recipe_name', $this->recipe_name);
        $stmt->bindParam(':cook_time', $this->cook_time);
        $stmt->bindParam(':ingredients', $this->ingredients);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':steps', $this->steps);
        $stmt->bindParam(':created_at', $this->created_at);
        $stmt->bindParam(':updated_at', $this->updated_at);
        
        // クエリ実行
        if($stmt->execute()) {
            $this->recipe_id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }

    // レシピ更新
    public function updateRecipe() {
        // 現在の日時
        $this->updated_at = date('Y-m-d H:i:s');
        
        // クエリ
        $query = 'UPDATE ' . $this->table . '
                SET
                    recipe_name = :recipe_name,
                    cook_time = :cook_time,
                    material = :ingredients,
                    explanation = :description,
                    steps = :steps,
                    updated_at = :updated_at
                WHERE
                    recipe_id = :recipe_id';

        // ステートメント準備
        $stmt = $this->conn->prepare($query);
        
        // パラメータのクリーニング
        $this->recipe_name = htmlspecialchars(strip_tags($this->recipe_name));
        $this->cook_time = htmlspecialchars(strip_tags($this->cook_time));
        $this->ingredients = htmlspecialchars(strip_tags($this->ingredients));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->steps = htmlspecialchars(strip_tags($this->steps));
        $this->recipe_id = htmlspecialchars(strip_tags($this->recipe_id));
        
        // パラメータをバインド
        $stmt->bindParam(':recipe_name', $this->recipe_name);
        $stmt->bindParam(':cook_time', $this->cook_time);
        $stmt->bindParam(':ingredients', $this->ingredients);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':steps', $this->steps);
        $stmt->bindParam(':updated_at', $this->updated_at);
        $stmt->bindParam(':recipe_id', $this->recipe_id);
        
        // クエリ実行
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    // レシピ削除
    public function deleteRecipe() {
        // クエリ
        $query = 'DELETE FROM ' . $this->table . ' WHERE recipe_id = :recipe_id';

        // ステートメント準備
        $stmt = $this->conn->prepare($query);
        
        // ID クリーニング
        $this->recipe_id = htmlspecialchars(strip_tags($this->recipe_id));
        
        // パラメータをバインド
        $stmt->bindParam(':recipe_id', $this->recipe_id);
        
        // クエリ実行
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    // レシピ検索
    public function searchRecipes($criteria = []) {
        // 基本クエリ
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
                    ' . $this->table . ' r';
        
        // 検索条件がある場合
        $whereClause = [];
        $params = [];
        
        if(!empty($criteria['search'])) {
            $whereClause[] = '(r.recipe_name LIKE ? OR r.explanation LIKE ? OR r.material LIKE ? OR r.steps LIKE ?)';
            $searchTerm = '%' . $criteria['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        // タグで絞り込み
        if(!empty($criteria['tag_id'])) {
            $query .= ' INNER JOIN RecipeTag rt ON r.recipe_id = rt.recipe_id';
            $whereClause[] = 'rt.tag_id = ?';
            $params[] = $criteria['tag_id'];
        }
        
        // WHERE句の組み立て
        if(!empty($whereClause)) {
            $query .= ' WHERE ' . implode(' AND ', $whereClause);
        }
        
        // 並び替え
        if(!empty($criteria['sort_field']) && !empty($criteria['sort_order'])) {
            $allowedFields = ['recipe_name', 'cook_time', 'created_at'];
            $allowedOrders = ['asc', 'desc'];
            
            $sortField = in_array($criteria['sort_field'], $allowedFields) ? $criteria['sort_field'] : 'created_at';
            $sortOrder = in_array($criteria['sort_order'], $allowedOrders) ? $criteria['sort_order'] : 'desc';
            
            $query .= ' ORDER BY r.' . $sortField . ' ' . strtoupper($sortOrder);
        } else {
            // デフォルト並び順
            $query .= ' ORDER BY r.created_at DESC';
        }
        
        // ステートメント準備
        $stmt = $this->conn->prepare($query);
        
        // パラメータをバインド
        foreach ($params as $index => $param) {
            $stmt->bindParam($index + 1, $param);
        }
        
        // クエリ実行
        $stmt->execute();
        
        return $stmt;
    }

    // レシピ並べ替え
    public function sortRecipes($field = 'created_at', $order = 'desc') {
        // 許可されたフィールドと順序
        $allowedFields = ['recipe_name', 'cook_time', 'created_at'];
        $allowedOrders = ['asc', 'desc'];
        
        $sortField = in_array($field, $allowedFields) ? $field : 'created_at';
        $sortOrder = in_array($order, $allowedOrders) ? $order : 'desc';
        
        // クエリ
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
                    ' . $this->table . ' r
                ORDER BY
                    r.' . $sortField . ' ' . strtoupper($sortOrder);
        
        // ステートメント準備
        $stmt = $this->conn->prepare($query);
        
        // クエリ実行
        $stmt->execute();
        
        return $stmt;
    }
}
?>
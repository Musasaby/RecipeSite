<?php
/**
 * レシピ情報を管理するクラス
 */
class RecipeManager {
    private $db;
    
    /**
     * コンストラクタ
     * @param PDO $db データベース接続
     */
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * レシピを取得（全件または指定ID）
     * @param int|null $id レシピID（nullの場合は全件取得）
     * @return array レシピ情報の配列
     */
    public function getRecipes($id = null) {
        if ($id !== null) {
            // 指定IDのレシピを取得
            $stmt = $this->db->prepare("SELECT * FROM recipes WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            // 全レシピを取得
            $stmt = $this->db->query("SELECT * FROM recipes ORDER BY created_at DESC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    
    /**
     * レシピを作成
     * @param string $title レシピのタイトル
     * @param string $content レシピの内容
     * @return int 作成されたレシピのID
     */
    public function createRecipe($title, $content) {
        $stmt = $this->db->prepare("INSERT INTO recipes (title, content) VALUES (?, ?)");
        $stmt->execute([$title, $content]);
        return $this->db->lastInsertId();
    }
    
    /**
     * レシピを更新
     * @param int $id レシピID
     * @param string $title レシピのタイトル
     * @param string $content レシピの内容
     * @return bool 成功した場合はtrue
     */
    public function updateRecipe($id, $title, $content) {
        $stmt = $this->db->prepare("UPDATE recipes SET title = ?, content = ? WHERE id = ?");
        return $stmt->execute([$title, $content, $id]);
    }
    
    /**
     * レシピを削除
     * @param int $id レシピID
     * @return bool 成功した場合はtrue
     */
    public function deleteRecipe($id) {
        $stmt = $this->db->prepare("DELETE FROM recipes WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * レシピの画像情報を取得
     * @param int $recipeId レシピID
     * @return array 画像情報の配列
     */
    public function getRecipeImages($recipeId) {
        $stmt = $this->db->prepare("SELECT * FROM images WHERE recipe_id = ? ORDER BY is_main DESC");
        $stmt->execute([$recipeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * レシピにメイン画像を登録
     * @param int $recipeId レシピID
     * @param string $filename ファイル名
     * @param string $altText 代替テキスト
     * @return int 登録された画像のID
     */
    public function addRecipeImage($recipeId, $filename, $altText = null, $isMain = false) {
        // もしメイン画像として設定する場合、既存のメイン画像を解除
        if ($isMain) {
            $resetStmt = $this->db->prepare("UPDATE images SET is_main = FALSE WHERE recipe_id = ?");
            $resetStmt->execute([$recipeId]);
        }
        
        $stmt = $this->db->prepare("INSERT INTO images (recipe_id, filename, alt_text, is_main) VALUES (?, ?, ?, ?)");
        $stmt->execute([$recipeId, $filename, $altText, $isMain]);
        return $this->db->lastInsertId();
    }
    
    /**
     * レシピ画像を削除
     * @param int $imageId 画像ID
     * @return bool 成功した場合はtrue
     */
    public function deleteRecipeImage($imageId) {
        $stmt = $this->db->prepare("DELETE FROM images WHERE id = ?");
        return $stmt->execute([$imageId]);
    }
}
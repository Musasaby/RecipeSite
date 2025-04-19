<?php
/**
 * タグ情報を管理するクラス
 */
class TagManager {
    private $db;
    
    /**
     * コンストラクタ
     * @param PDO $db データベース接続
     */
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * すべてのタグを取得
     * @return array タグ情報の配列
     */
    public function getAllTags() {
        $stmt = $this->db->query("SELECT * FROM tags ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * タグを名前で検索または作成
     * @param string $tagName タグ名
     * @return int タグID
     */
    public function getOrCreateTag($tagName) {
        // タグ名が空の場合は処理しない
        if (empty(trim($tagName))) {
            return null;
        }
        
        // まず既存のタグを検索
        $stmt = $this->db->prepare("SELECT id FROM tags WHERE name = ?");
        $stmt->execute([trim($tagName)]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            return $result['id'];
        } else {
            // 存在しない場合は新規作成
            $stmt = $this->db->prepare("INSERT INTO tags (name) VALUES (?)");
            $stmt->execute([trim($tagName)]);
            return $this->db->lastInsertId();
        }
    }
    
    /**
     * レシピに関連するタグを取得
     * @param int $recipeId レシピID
     * @return array タグ情報の配列
     */
    public function getRecipeTags($recipeId) {
        $stmt = $this->db->prepare("
            SELECT t.* 
            FROM tags t
            JOIN recipe_tags rt ON t.id = rt.tag_id
            WHERE rt.recipe_id = ?
            ORDER BY t.name
        ");
        $stmt->execute([$recipeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * レシピにタグを追加
     * @param int $recipeId レシピID
     * @param array $tagIds タグIDの配列
     * @return bool 成功した場合はtrue
     */
    public function addTagsToRecipe($recipeId, $tagIds) {
        // 重複を防ぐために既存のタグ関連を削除
        $this->removeAllTagsFromRecipe($recipeId);
        
        // 新しいタグを追加
        $stmt = $this->db->prepare("INSERT INTO recipe_tags (recipe_id, tag_id) VALUES (?, ?)");
        
        foreach ($tagIds as $tagId) {
            if (!empty($tagId)) {
                $stmt->execute([$recipeId, $tagId]);
            }
        }
        
        return true;
    }
    
    /**
     * レシピからすべてのタグを削除
     * @param int $recipeId レシピID
     * @return bool 成功した場合はtrue
     */
    public function removeAllTagsFromRecipe($recipeId) {
        $stmt = $this->db->prepare("DELETE FROM recipe_tags WHERE recipe_id = ?");
        return $stmt->execute([$recipeId]);
    }
    
    /**
     * レシピから特定のタグを削除
     * @param int $recipeId レシピID
     * @param int $tagId タグID
     * @return bool 成功した場合はtrue
     */
    public function removeTagFromRecipe($recipeId, $tagId) {
        $stmt = $this->db->prepare("DELETE FROM recipe_tags WHERE recipe_id = ? AND tag_id = ?");
        return $stmt->execute([$recipeId, $tagId]);
    }
    
    /**
     * 指定されたタグを持つレシピを検索
     * @param array $tagIds タグIDの配列
     * @return array レシピIDの配列
     */
    public function findRecipesByTags($tagIds) {
        $placeholders = implode(',', array_fill(0, count($tagIds), '?'));
        $sql = "
            SELECT recipe_id, COUNT(tag_id) as tag_count
            FROM recipe_tags
            WHERE tag_id IN ({$placeholders})
            GROUP BY recipe_id
            ORDER BY tag_count DESC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($tagIds);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
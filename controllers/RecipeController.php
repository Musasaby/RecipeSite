<?php
require_once '../models/Recipe.php';
require_once '../models/Tag.php';
require_once '../models/RecipeTag.php';

class RecipeController {
    // レシピの投稿処理
    public function handleRecipeSubmission() {
        // POSTリクエストであることを確認
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); // Method Not Allowed
            echo json_encode(['message' => 'POSTメソッドのみ許可されています']);
            return;
        }
        
        // POSTデータを取得
        $data = json_decode(file_get_contents("php://input"));
        
        // データの検証
        if (!isset($data->recipe_name) || !isset($data->cook_time) || !isset($data->ingredients) || !isset($data->steps)) {
            http_response_code(400); // Bad Request
            echo json_encode(['message' => '必須フィールドがありません']);
            return;
        }
        
        // 新規レシピオブジェクト作成
        $recipe = new Recipe();
        $recipe->recipe_name = $data->recipe_name;
        $recipe->cook_time = $data->cook_time;
        $recipe->ingredients = $data->ingredients;
        $recipe->steps = $data->steps;
        $recipe->description = isset($data->description) ? $data->description : '';
        
        // 既存レシピの更新の場合
        if(isset($data->recipe_id) && !empty($data->recipe_id)) {
            $recipe->recipe_id = $data->recipe_id;
            
            // レシピを更新
            if($recipe->updateRecipe()) {
                // 既存のタグ関連を削除
                $recipeTag = new RecipeTag();
                $recipeTag->recipe_id = $recipe->recipe_id;
                $recipeTag->removeAllTagsFromRecipe();
                
                // 新しいタグを処理
                if(isset($data->tags) && is_array($data->tags) && !empty($data->tags)) {
                    $this->processRecipeTags($recipe->recipe_id, $data->tags);
                }
                
                // 成功レスポンス
                http_response_code(200);
                echo json_encode([
                    'message' => 'レシピが更新されました',
                    'recipe_id' => $recipe->recipe_id
                ]);
            } else {
                // エラーレスポンス
                http_response_code(500);
                echo json_encode(['message' => 'レシピの更新に失敗しました']);
            }
        } 
        // 新規レシピ登録の場合
        else {
            // レシピを作成
            if($recipe->createRecipe()) {
                // タグを処理
                if(isset($data->tags) && is_array($data->tags) && !empty($data->tags)) {
                    $this->processRecipeTags($recipe->recipe_id, $data->tags);
                }
                
                // 成功レスポンス
                http_response_code(201); // Created
                echo json_encode([
                    'message' => 'レシピが登録されました',
                    'recipe_id' => $recipe->recipe_id
                ]);
            } else {
                // エラーレスポンス
                http_response_code(500);
                echo json_encode(['message' => 'レシピの登録に失敗しました']);
            }
        }
    }

    // レシピの詳細表示
    public function displayRecipeDetails($recipe_id) {
        // レシピIDが指定されていることを確認
        if(empty($recipe_id)) {
            http_response_code(400);
            echo json_encode(['message' => 'レシピIDが必要です']);
            return;
        }
        
        // レシピオブジェクトの作成
        $recipe = new Recipe();
        
        // レシピ詳細を取得
        if($recipe->getRecipeDetails($recipe_id)) {
            // タグを取得
            $recipeTag = new RecipeTag();
            $tags_result = $recipeTag->getTagsByRecipe($recipe_id);
            $tags = [];
            
            while($tag_row = $tags_result->fetch(PDO::FETCH_ASSOC)) {
                $tags[] = [
                    'id' => $tag_row['tag_id'],
                    'name' => $tag_row['tag_name']
                ];
            }
            
            // レシピ情報をJSON形式で返す
            $recipe_data = [
                'recipe_id' => $recipe->recipe_id,
                'recipe_name' => $recipe->recipe_name,
                'cook_time' => $recipe->cook_time,
                'ingredients' => $recipe->ingredients,
                'description' => $recipe->description,
                'steps' => $recipe->steps,
                'created_at' => $recipe->created_at,
                'updated_at' => $recipe->updated_at,
                'tags' => $tags
            ];
            
            http_response_code(200);
            echo json_encode($recipe_data);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'レシピが見つかりません']);
        }
    }

    // レシピを検索
    public function searchRecipes($params) {
        // レシピオブジェクトの作成
        $recipe = new Recipe();
        
        // 検索基準を設定
        $search_criteria = [];
        
        if(isset($params['search']) && !empty($params['search'])) {
            $search_criteria['search'] = $params['search'];
        }
        
        if(isset($params['tag_id']) && !empty($params['tag_id'])) {
            $search_criteria['tag_id'] = $params['tag_id'];
        }
        
        if(isset($params['sort_field']) && isset($params['sort_order'])) {
            $search_criteria['sort_field'] = $params['sort_field'];
            $search_criteria['sort_order'] = $params['sort_order'];
        }
        
        // 検索結果を取得
        $result = $recipe->searchRecipes($search_criteria);
        
        // 結果を準備
        $recipes_arr = [];
        $recipes_arr['records'] = [];
        
        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
            // レシピの基本情報
            $recipe_item = [
                'recipe_id' => $row['recipe_id'],
                'recipe_name' => $row['recipe_name'],
                'cook_time' => $row['cook_time'],
                'ingredients' => $row['ingredients'],
                'description' => $row['description'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at']
            ];
            
            // タグ情報を取得（レシピごと）
            $recipeTag = new RecipeTag();
            $tags_result = $recipeTag->getTagsByRecipe($row['recipe_id']);
            $tags = [];
            
            while($tag_row = $tags_result->fetch(PDO::FETCH_ASSOC)) {
                $tags[] = [
                    'id' => $tag_row['tag_id'],
                    'name' => $tag_row['tag_name']
                ];
            }
            
            $recipe_item['tags'] = $tags;
            
            // レシピをリストに追加
            array_push($recipes_arr['records'], $recipe_item);
        }
        
        http_response_code(200);
        echo json_encode($recipes_arr);
    }

    // レシピをソート
    public function sortRecipes($field, $order) {
        // レシピオブジェクトの作成
        $recipe = new Recipe();
        
        // ソート結果を取得
        $result = $recipe->sortRecipes($field, $order);
        
        // 結果を準備
        $recipes_arr = [];
        $recipes_arr['records'] = [];
        
        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
            // レシピの基本情報
            $recipe_item = [
                'recipe_id' => $row['recipe_id'],
                'recipe_name' => $row['recipe_name'],
                'cook_time' => $row['cook_time'],
                'ingredients' => $row['ingredients'],
                'description' => $row['description'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at']
            ];
            
            // タグ情報を取得（レシピごと）
            $recipeTag = new RecipeTag();
            $tags_result = $recipeTag->getTagsByRecipe($row['recipe_id']);
            $tags = [];
            
            while($tag_row = $tags_result->fetch(PDO::FETCH_ASSOC)) {
                $tags[] = [
                    'id' => $tag_row['tag_id'],
                    'name' => $tag_row['tag_name']
                ];
            }
            
            $recipe_item['tags'] = $tags;
            
            // レシピをリストに追加
            array_push($recipes_arr['records'], $recipe_item);
        }
        
        http_response_code(200);
        echo json_encode($recipes_arr);
    }

    // すべてのレシピを一覧表示
    public function listRecipes() {
        // レシピオブジェクトの作成
        $recipe = new Recipe();
        
        // デフォルト並び順で取得
        $result = $recipe->sortRecipes('created_at', 'desc');
        
        // 結果を準備
        $recipes_arr = [];
        $recipes_arr['records'] = [];
        
        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
            // レシピの基本情報
            $recipe_item = [
                'recipe_id' => $row['recipe_id'],
                'recipe_name' => $row['recipe_name'],
                'cook_time' => $row['cook_time'],
                'ingredients' => $row['ingredients'],
                'description' => $row['description'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at']
            ];
            
            // タグ情報を取得（レシピごと）
            $recipeTag = new RecipeTag();
            $tags_result = $recipeTag->getTagsByRecipe($row['recipe_id']);
            $tags = [];
            
            while($tag_row = $tags_result->fetch(PDO::FETCH_ASSOC)) {
                $tags[] = [
                    'id' => $tag_row['tag_id'],
                    'name' => $tag_row['tag_name']
                ];
            }
            
            $recipe_item['tags'] = $tags;
            
            // レシピをリストに追加
            array_push($recipes_arr['records'], $recipe_item);
        }
        
        http_response_code(200);
        echo json_encode($recipes_arr);
    }
    
    // タグ削除
    public function deleteRecipe($recipe_id) {
        // レシピIDが指定されていることを確認
        if(empty($recipe_id)) {
            http_response_code(400);
            echo json_encode(['message' => 'レシピIDが必要です']);
            return;
        }
        
        // まず関連タグをすべて削除
        $recipeTag = new RecipeTag();
        $recipeTag->recipe_id = $recipe_id;
        $recipeTag->removeAllTagsFromRecipe();
        
        // レシピを削除
        $recipe = new Recipe();
        $recipe->recipe_id = $recipe_id;
        
        if($recipe->deleteRecipe()) {
            http_response_code(200);
            echo json_encode(['message' => 'レシピが削除されました']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'レシピの削除に失敗しました']);
        }
    }
    
    // タグ処理の共通ヘルパーメソッド
    private function processRecipeTags($recipe_id, $tags) {
        if(empty($tags)) return;
        
        // タグがID配列か文字列配列かを判断
        if(is_numeric($tags[0])) {
            // 数値配列の場合、既にタグIDとして扱う
            foreach($tags as $tag_id) {
                // タグとレシピの関連付け
                $recipeTag = new RecipeTag();
                $recipeTag->recipe_id = $recipe_id;
                $recipeTag->tag_id = $tag_id;
                $recipeTag->addTagToRecipe();
            }
        } else {
            // 文字列配列の場合、タグ名として処理
            foreach($tags as $tag_name) {
                // 空のタグは無視
                if(empty($tag_name)) continue;
                
                // タグオブジェクト作成
                $tag = new Tag();
                $tag->tag_name = trim($tag_name);
                
                // タグを作成（既存の場合は既存のIDを取得）
                if($tag->createTag()) {
                    // レシピとタグの関連付け
                    $recipeTag = new RecipeTag();
                    $recipeTag->recipe_id = $recipe_id;
                    $recipeTag->tag_id = $tag->tag_id;
                    $recipeTag->addTagToRecipe();
                }
            }
        }
    }
    
    // 全タグの取得
    public function getAllTags() {
        $tag = new Tag();
        $result = $tag->getAllTags();
        
        $tags_arr = [];
        $tags_arr['records'] = [];
        
        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $tag_item = [
                'id' => $row['tag_id'],
                'name' => $row['tag_name']
            ];
            
            array_push($tags_arr['records'], $tag_item);
        }
        
        http_response_code(200);
        echo json_encode($tags_arr);
    }
}
?>
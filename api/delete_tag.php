<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

// DELETE以外のリクエストの場合はエラーを返す
$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'DELETE') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => '許可されていないメソッドです。DELETEメソッドを使用してください。']);
    exit();
}

// POSTデータを取得
$data = json_decode(file_get_contents("php://input"), true);

// タグIDが提供されているか確認
if (!isset($data['tag_id']) || empty($data['tag_id'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'タグIDが必要です。']);
    exit();
}

// データベース接続
require_once "../models/Database.php";
require_once "../models/Tag.php";
require_once "../models/RecipeTag.php";

try {
    $database = new Database();
    $db = $database->connect();
    
    $tag = new Tag($db);
    $recipeTag = new RecipeTag($db);
    
    // タグIDを設定
    $tag->tag_id = $data['tag_id'];
    
    // このタグを使用しているレシピがあるか確認
    $usageCount = $recipeTag->countRecipesByTagId($tag->tag_id);
    if ($usageCount > 0) {
        http_response_code(400);
        echo json_encode([
            'error' => 'このタグは' . $usageCount . '件のレシピで使用されているため削除できません。'
        ]);
        exit();
    }
    
    // タグを削除
    if ($tag->delete()) {
        http_response_code(200);
        echo json_encode(['message' => 'タグが削除されました']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'タグの削除に失敗しました']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
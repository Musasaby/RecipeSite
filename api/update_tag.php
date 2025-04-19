<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

// PUTまたはPOST以外のリクエストの場合はエラーを返す
$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'PUT' && $method !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => '許可されていないメソッドです。PUTメソッドを使用してください。']);
    exit();
}

// POSTデータを取得
$data = json_decode(file_get_contents("php://input"), true);

// タグIDとタグ名が提供されているか確認
if (!isset($data['tag_id']) || empty($data['tag_id']) || !isset($data['tag_name']) || empty($data['tag_name'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'タグIDとタグ名の両方が必要です。']);
    exit();
}

// データベース接続
require_once "../models/Database.php";
require_once "../models/Tag.php";

try {
    $database = new Database();
    $db = $database->connect();
    
    $tag = new Tag($db);
    
    // タグIDとタグ名を設定
    $tag->tag_id = $data['tag_id'];
    $tag->tag_name = $data['tag_name'];
    
    // 同じ名前のタグが存在するか確認（自分自身は除く）
    if ($tag->existsWithSameNameExcludingSelf()) {
        http_response_code(400);
        echo json_encode(['error' => '同じ名前のタグが既に存在します']);
        exit();
    }
    
    // タグを更新
    if ($tag->update()) {
        http_response_code(200);
        echo json_encode([
            'message' => 'タグが更新されました',
            'tag_id' => $tag->tag_id,
            'tag_name' => $tag->tag_name
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'タグの更新に失敗しました']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
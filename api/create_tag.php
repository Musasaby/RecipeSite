<?php
// ヘッダー設定
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// OPTIONSリクエストに対してはここで処理を終了
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// コントローラーの読み込み
require_once '../models/Tag.php';

// POSTリクエストかどうか確認
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // POSTデータを取得
    $data = json_decode(file_get_contents("php://input"));
    
    // データの検証
    if (!isset($data->tag_name) || empty($data->tag_name)) {
        http_response_code(400);
        echo json_encode(['message' => 'タグ名が必要です']);
        exit();
    }
    
    // 新規タグオブジェクト作成
    $tag = new Tag();
    $tag->tag_name = $data->tag_name;
    
    // タグを作成
    if ($tag->createTag()) {
        http_response_code(201);
        echo json_encode([
            'message' => 'タグが登録されました',
            'tag_id' => $tag->tag_id,
            'tag_name' => $tag->tag_name
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['message' => 'タグの登録に失敗しました']);
    }
} else {
    // POST以外のメソッドは許可しない
    http_response_code(405);
    echo json_encode(['message' => 'このエンドポイントではPOSTメソッドのみ許可されています']);
}
?>
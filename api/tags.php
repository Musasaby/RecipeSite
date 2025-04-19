<?php
// ヘッダー設定
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// モデルの読み込み
require_once '../models/Database.php';
require_once '../models/Tag.php';

// リクエストメソッドがGETかどうか確認
if($_SERVER['REQUEST_METHOD'] == 'GET') {
    try {
        // データベース接続
        $database = new Database();
        $db = $database->connect();
        
        // タグモデルのインスタンス化
        $tag = new Tag($db);
        
        // すべてのタグを取得
        $result = $tag->getAllTags();
        
        $tags_arr = [];
        
        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
            // フロントエンドが期待する形式(tag_id, tag_name)で返す
            $tag_item = [
                'tag_id' => $row['tag_id'],
                'tag_name' => $row['tag_name']
            ];
            
            array_push($tags_arr, $tag_item);
        }
        
        http_response_code(200);
        echo json_encode($tags_arr);
    }
    catch(Exception $e) {
        http_response_code(500);
        echo json_encode([
            "message" => "タグの取得に失敗しました: " . $e->getMessage()
        ]);
    }
} else {
    // GETメソッド以外は許可しない
    http_response_code(405);
    echo json_encode(["message" => "このエンドポイントではGETメソッドのみ許可されています"]);
}
?>
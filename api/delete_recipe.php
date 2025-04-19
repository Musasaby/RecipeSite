<?php
// ヘッダー設定
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// OPTIONSリクエストに対してはここで処理を終了
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// コントローラーの読み込み
require_once '../controllers/RecipeController.php';

// レシピコントローラーのインスタンス化
$controller = new RecipeController();

// リクエストメソッドがDELETEかどうか確認
if($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    // レシピIDを取得
    $data = json_decode(file_get_contents("php://input"));
    
    if(isset($data->recipe_id) && !empty($data->recipe_id)) {
        $controller->deleteRecipe($data->recipe_id);
    } else {
        // IDがない場合はエラー
        http_response_code(400);
        echo json_encode(["message" => "レシピIDが必要です"]);
    }
} else {
    // 許可されていないメソッド
    http_response_code(405);
    echo json_encode(["message" => "このエンドポイントではDELETEメソッドのみ許可されています"]);
}
?>
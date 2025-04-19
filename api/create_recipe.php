<?php
// ヘッダー設定
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, PUT, OPTIONS");
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

// リクエストメソッドがPOSTかPUTかどうか確認
if($_SERVER['REQUEST_METHOD'] == 'POST' || $_SERVER['REQUEST_METHOD'] == 'PUT') {
    $controller->handleRecipeSubmission();
} else {
    // 許可されていないメソッド
    http_response_code(405);
    echo json_encode(["message" => "このエンドポイントではPOST/PUTメソッドのみ許可されています"]);
}
?>
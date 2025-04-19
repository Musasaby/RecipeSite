<?php
// ヘッダー設定
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// コントローラーの読み込み
require_once '../controllers/RecipeController.php';

// レシピコントローラーのインスタンス化
$controller = new RecipeController();

// リクエストメソッドがGETかどうか確認
if($_SERVER['REQUEST_METHOD'] == 'GET') {
    // 検索パラメータがある場合は検索として扱う
    if(isset($_GET['search']) || isset($_GET['tag_id']) || (isset($_GET['sort_field']) && isset($_GET['sort_order']))) {
        $controller->searchRecipes($_GET);
    } 
    // 特定のレシピIDが指定されている場合はその詳細を取得
    else if(isset($_GET['id']) && !empty($_GET['id'])) {
        $controller->displayRecipeDetails($_GET['id']);
    } 
    // それ以外の場合は全レシピをリスト表示
    else {
        $controller->listRecipes();
    }
} else {
    // GETメソッド以外は許可しない
    http_response_code(405);
    echo json_encode(["message" => "このエンドポイントではGETメソッドのみ許可されています"]);
}
?>
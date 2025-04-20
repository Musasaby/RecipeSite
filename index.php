<?php
// アプリケーションクラスを読み込み
require_once 'classes/App.php';

// アプリケーションのインスタンスを作成
$app = new App();

// フォーム送信処理
$app->handleRecipeCreate();
$app->handleRecipeDelete();

// 現在のページを取得
$currentPage = $app->getCurrentPage();

// ページタイトル
$title = "レシピ管理システム";

// ヘッダーを表示
require_once 'templates/header.php';

// メインコンテンツを表示
$app->displayContent();

// フッターを表示
require_once 'templates/footer.php';
?>
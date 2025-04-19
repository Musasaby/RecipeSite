<?php
// アプリケーションクラスを読み込み
require_once 'classes/App.php';

// アプリケーションのインスタンスを作成
$app = new App();

// フォーム送信処理
$app->handleRecipeCreate();
$app->handleRecipeDelete();

// ページタイトル
$title = "レシピ管理システム";
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 5px;
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        .recipe-form {
            background: #f9f9f9;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 5px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input[type="text"],
        .form-group textarea {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .recipe-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .recipe-card {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            background: white;
        }
        .recipe-image img {
            max-width: 100%;
            height: auto;
            border-radius: 4px;
        }
        .recipe-content {
            margin: 10px 0;
        }
        .recipe-tags {
            color: #666;
            font-size: 0.9em;
            margin: 10px 0;
        }
        .recipe-actions {
            margin-top: 15px;
            text-align: right;
        }
        .recipe-actions button {
            background-color: #ff4c4c;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
        }
        button[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button[type="submit"]:hover {
            background-color: #45a049;
        }
        hr {
            border: 0;
            border-top: 1px solid #ddd;
            margin: 30px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><?php echo htmlspecialchars($title); ?></h1>

        <!-- レシピ登録フォーム -->
        <?php $app->displayRecipeForm(); ?>

        <hr>

        <h2>登録済みレシピ一覧</h2>
        <!-- レシピ一覧 -->
        <?php $app->displayRecipeList(); ?>
    </div>
</body>
</html>
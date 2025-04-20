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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
</head>
<body class="bg-light">
    <div class="container py-4">
        <header class="pb-3 mb-4 border-bottom">
            <h1 class="display-5 fw-bold text-center"><?php echo htmlspecialchars($title); ?></h1>
        </header>

        <!-- レシピ登録フォーム -->
        <?php $app->displayRecipeForm(); ?>

        <div class="my-4">
            <h2 class="mb-3">登録済みレシピ一覧</h2>
            <!-- レシピ一覧 -->
            <?php $app->displayRecipeList(); ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
</body>
</html>
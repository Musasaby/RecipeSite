<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light pt-5 mt-5"> <!-- パディング追加 -->
    <nav class="navbar navbar-expand-sm navbar-light bg-light fixed-top shadow-sm"> <!-- fixed-topとshadow-sm追加 -->
        <div class="container">
            <a class="navbar-brand" href="<?php echo $app->getPageUrl('home'); ?>"><?php echo htmlspecialchars($title); ?></a>
            <button
                class="navbar-toggler d-lg-none"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#collapsibleNavId"
                aria-controls="collapsibleNavId"
                aria-expanded="false"
                aria-label="Toggle navigation"
            >
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="collapsibleNavId">
                <ul class="navbar-nav me-auto mt-2 mt-lg-0">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($currentPage === 'home' ? 'active' : ''); ?>" 
                           href="<?php echo $app->getPageUrl('home'); ?>" 
                           aria-current="page">レシピ一覧
                            <?php if ($currentPage === 'home'): ?><span class="visually-hidden">(current)</span><?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($currentPage === 'create' ? 'active' : ''); ?>" 
                           href="<?php echo $app->getPageUrl('create'); ?>">
                           新規レシピ登録
                           <?php if ($currentPage === 'create'): ?><span class="visually-hidden">(current)</span><?php endif; ?>
                        </a>
                    </li>
                </ul>
                <form class="d-flex my-2 my-lg-0">
                    <input
                        class="form-control me-sm-2"
                        type="text"
                        placeholder="Search"
                    />
                    <button
                        class="btn btn-outline-success my-2 my-sm-0"
                        type="submit"
                    >
                        Search
                    </button>
                </form>
            </div>
        </div>
    </nav>
    
    <div class="container py-4">
        <header class="pb-3 mb-4 border-bottom page-header">
            <h1 class="display-5 fw-bold text-center"><?php echo htmlspecialchars($title); ?></h1>
        </header>
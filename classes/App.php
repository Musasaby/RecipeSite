<?php
/**
 * アプリケーションのメインロジックを管理するクラス
 */
class App {
    private const POST_PARAM_TITLE = 'title';
    private const POST_PARAM_CONTENT = 'content';
    private const POST_PARAM_TAGS = 'tags';
    private const POST_PARAM_IMAGE = 'image';
    private const POST_PARAM_ACTION = 'action';
    private const POST_PARAM_RECIPE_ID = 'recipe_id';
    private const POST_PARAM_CREATE_RECIPE = 'create_recipe';
    private const POST_PARAM_DELETE_RECIPE = 'delete_recipe';


    private $db;
    private $recipeManager;
    private $tagManager;
    
    /**
     * コンストラクタ
     */
    public function __construct() {
        // 必要なクラスファイルを読み込み
        $this->loadClasses();
        
        try {
            // データベース接続を取得
            $this->db = DatabaseConnection::getInstance()->getConnection();
            
            // マネージャーインスタンスを初期化
            $this->recipeManager = new RecipeManager($this->db);
            $this->tagManager = new TagManager($this->db);
            
        } catch (Exception $e) {
            $this->displayError($e->getMessage());
            exit;
        }
    }
    
    /**
     * 必要なクラスファイルを読み込み
     * includeのようなもの
     * https://qiita.com/siroisitaka/items/6c61f9243220036577e8
     */
    private function loadClasses() {
        require_once 'classes/Config.php';
        require_once 'classes/DatabaseConnection.php';
        require_once 'classes/FileUploader.php';
        require_once 'classes/RecipeManager.php';
        require_once 'classes/TagManager.php';
    }
    
    /**
     * エラーメッセージを表示
     */
    public function displayError($message) {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
        echo htmlspecialchars($message);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
    }
    
    /**
     * メッセージを表示
     */
    public function displayMessage($message) {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
        echo htmlspecialchars($message);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
    }
    
    /**
     * POSTリクエストかどうかをチェック
     */
    public function isPostRequest() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
    
    /**
     * GETリクエストかどうかをチェック
     */
    public function isGetRequest() {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }
    
    /**
     * POSTパラメータを安全に取得
     * trim関数を使用して前後の空白を削除
     * ユーザー側に表示しない場合はPOSTを使用
     */
    public function getPostParam($key, $default = '') {
        return isset($_POST[$key]) ? trim($_POST[$key]) : $default;
    }
    
    /**
     * GETパラメータを安全に取得
     * URL等ユーザー側に表示したい場合はGETを使用
     */
    public function getGetParam($key, $default = '') {
        return isset($_GET[$key]) ? trim($_GET[$key]) : $default;
    }
    
    /**
     * アップロードされた画像を処理
     */
    public function handleFileUpload() {
        if (isset($_FILES[self::POST_PARAM_IMAGE]) && $_FILES[self::POST_PARAM_IMAGE]['error'] === UPLOAD_ERR_OK) {
            $uploader = new FileUploader();
            $result = $uploader->upload($_FILES[self::POST_PARAM_IMAGE]);
            
            if ($result['success']) {
                return $result['filename'];
            } else {
                $this->displayError($result['message']);
                return null;
            }
        }
        return null;
    }
    
    /**
     * レシピの作成処理
     */
    public function handleRecipeCreate() {
        // POSTリクエストでない、またはアクションがcreate_recipeでない場合は早期リターン
        //フォームから送信されたとき、actionがcreate_recipeでない場合は早期リターン
        if (!$this->isPostRequest() || !isset($_POST[self::POST_PARAM_ACTION]) || $_POST[self::POST_PARAM_ACTION] !== self::POST_PARAM_CREATE_RECIPE) {
            return false;
        }
        
        $title = $this->getPostParam(self::POST_PARAM_TITLE);
        $content = $this->getPostParam(self::POST_PARAM_CONTENT);
        
        // タイトルが空の場合は早期リターン
        if (empty($title)) {
            $this->displayError('タイトルは必須です');
            return false;
        }
        
        // レシピを作成
        $recipeId = $this->recipeManager->createRecipe($title, $content);
        
        // 画像のアップロード処理
        $filename = $this->handleFileUpload();
        if ($filename) {
            $this->recipeManager->addRecipeImage($recipeId, $filename, $title, true);
        }
        
        // タグの処理
        $tags = $this->getPostParam(self::POST_PARAM_TAGS);
        if (!empty($tags)) {
            $tagArray = array_map('trim', explode(',', $tags));
            $tagIds = [];
            
            foreach ($tagArray as $tagName) {
                if (!empty($tagName)) {
                    $tagIds[] = $this->tagManager->getOrCreateTag($tagName);
                }
            }
            
            if (!empty($tagIds)) {
                $this->tagManager->addTagsToRecipe($recipeId, $tagIds);
            }
        }
        
        $this->displayMessage('レシピを登録しました');
        return true;
    }
    
    /**
     * レシピの削除処理
     */
    public function handleRecipeDelete() {
        if ($this->isPostRequest() && isset($_POST[self::POST_PARAM_ACTION]) && $_POST[self::POST_PARAM_ACTION] === self::POST_PARAM_DELETE_RECIPE) {
            $recipeId = $this->getPostParam(self::POST_PARAM_RECIPE_ID);
            
            if (empty($recipeId)) {
                $this->displayError('レシピIDが指定されていません');
                return false;
            }
            
            // レシピを削除（外部キー制約により関連するタグと画像も削除される）
            if ($this->recipeManager->deleteRecipe($recipeId)) {
                $this->displayMessage('レシピを削除しました');
                return true;
            } else {
                $this->displayError('レシピの削除に失敗しました');
                return false;
            }
        }
        return false;
    }
    
    /**
     * レシピ一覧を取得して表示
     */
    public function displayRecipeList() {
        $recipes = $this->recipeManager->getRecipes();
        
        if (empty($recipes)) {
            echo '<div class="alert alert-info">登録されているレシピはありません。</div>';
            return;
        }
        
        echo '<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">';
        foreach ($recipes as $recipe) {
            $recipeId = $recipe['id'];
            
            // レシピの画像を取得
            $images = $this->recipeManager->getRecipeImages($recipeId);
            $mainImage = !empty($images) ? $images[0]['filename'] : null;
            
            // レシピのタグを取得
            $tags = $this->tagManager->getRecipeTags($recipeId);
            
            echo '<div class="col">';
            echo '<div class="card h-100">';
            
            if ($mainImage) {
                echo '<img src="uploads/' . htmlspecialchars($mainImage) . '" class="card-img-top" alt="' . htmlspecialchars($recipe[self::POST_PARAM_TITLE]) . '">';
            } else {
                echo '<div class="card-img-top bg-light text-center py-5">画像なし</div>';
            }
            
            echo '<div class="card-body">';
            echo '<h5 class="card-title">' . htmlspecialchars($recipe[self::POST_PARAM_TITLE]) . '</h5>';
            echo '<p class="card-text">' . htmlspecialchars(substr($recipe[self::POST_PARAM_CONTENT], 0, 100)) . (strlen($recipe[self::POST_PARAM_CONTENT]) > 100 ? '...' : '') . '</p>';
            
            if (!empty($tags)) {
                echo '<div class="mb-2">';
                foreach ($tags as $tag) {
                    echo '<span class="badge bg-secondary me-1">' . htmlspecialchars($tag['name']) . '</span>';
                }
                echo '</div>';
            }
            
            echo '</div>';
            echo '<div class="card-footer text-end">';
            echo '<form method="post">';
            echo '<input type="hidden" name="action" value="delete_recipe">';
            echo '<input type="hidden" name="recipe_id" value="' . $recipeId . '">';
            echo '<button type="submit" class="btn btn-danger btn-sm" onclick="return confirm(\'このレシピを削除してもよろしいですか？\');">削除</button>';
            echo '</form>';
            echo '</div>';
            
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
    }
    
    /**
     * レシピ登録フォームを表示
     */
    public function displayRecipeForm() {
        ?>
        <div class="card mb-4">
            <div class="card-header">
                <h2>新しいレシピを登録</h2>
            </div>
            <div class="card-body">
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="create_recipe">
                    
                    <div class="mb-3">
                        <label for="title" class="form-label">タイトル:</label>
                        <input type="text" id="title" name="title" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="content" class="form-label">内容:</label>
                        <textarea id="content" name="content" rows="4" class="form-control"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="image" class="form-label">画像:</label>
                        <input type="file" id="image" name="image" class="form-control" accept="image/jpeg,image/jpg,image/png,image/gif">
                    </div>
                    
                    <div class="mb-3">
                        <label for="tags" class="form-label">タグ (カンマ区切り):</label>
                        <input type="text" id="tags" name="tags" class="form-control" placeholder="例: パスタ, イタリアン, 簡単">
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-success">レシピを登録</button>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }
}
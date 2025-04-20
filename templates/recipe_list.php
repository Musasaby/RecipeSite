<h2 class="mb-3">登録済みレシピ一覧</h2>

<?php if (empty($recipes)): ?>
    <div class="alert alert-info">登録されているレシピはありません。</div>
<?php else: ?>
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php foreach ($recipes as $recipe): ?>
            <?php
            $recipeId = $recipe['id'];
            
            // レシピの画像を取得
            $images = $this->recipeManager->getRecipeImages($recipeId);
            $mainImage = !empty($images) ? $images[0]['filename'] : null;
            
            // レシピのタグを取得
            $tags = $this->tagManager->getRecipeTags($recipeId);
            ?>
            
            <div class="col">
                <div class="card h-100 recipe-card">
                    <?php if ($mainImage): ?>
                        <img src="uploads/<?php echo htmlspecialchars($mainImage); ?>" 
                             class="card-img-top" 
                             alt="<?php echo htmlspecialchars($recipe['title']); ?>">
                    <?php else: ?>
                        <div class="card-img-top no-image">画像なし</div>
                    <?php endif; ?>
                    
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($recipe['title']); ?></h5>
                        <p class="card-text">
                            <?php 
                            echo htmlspecialchars(substr($recipe['content'], 0, 100)) . 
                                (strlen($recipe['content']) > 100 ? '...' : ''); 
                            ?>
                        </p>
                        
                        <?php if (!empty($tags)): ?>
                            <div class="mb-2">
                                <?php foreach ($tags as $tag): ?>
                                    <span class="badge bg-secondary me-1 recipe-tag"><?php echo htmlspecialchars($tag['name']); ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="card-footer text-end">
                        <form method="post">
                            <input type="hidden" name="action" value="delete_recipe">
                            <input type="hidden" name="recipe_id" value="<?php echo $recipeId; ?>">
                            <button type="submit" class="btn btn-danger btn-sm" 
                                    onclick="return confirm('このレシピを削除してもよろしいですか？');">削除</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
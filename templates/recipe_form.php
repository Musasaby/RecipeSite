<div class="card mb-4 form-section">
    <div class="card-header">
        <h2>新しいレシピを登録</h2>
    </div>
    <div class="card-body">
        <div class="container">
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="create_recipe">
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="title" class="form-label">タイトル:</label>
                        <input type="text" id="title" name="title" class="form-control" required>
                    </div>
                    
                    <div class="col-md-4">
                        <label for="tags" class="form-label">タグ (カンマ区切り):</label>
                        <input type="text" id="tags" name="tags" class="form-control" placeholder="例: パスタ, イタリアン, 簡単">
                    </div>
                    <div class="col-md-4">
                        <label for="image" class="form-label">画像:</label>
                        <input type="file" id="image" name="image" class="form-control" accept="image/jpeg,image/jpg">
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label for="content" class="form-label">内容:</label>
                        <textarea id="content" name="content" rows="4" class="form-control"></textarea>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success">レシピを登録</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
$(document).ready(function() {
    // グローバル変数
    let currentRecipeId = null;
    let editMode = false;
    
    // 初期化: レシピ一覧の読み込み
    loadRecipes();
    
    // タグ一覧の読み込み
    loadTags();
    
    // レシピ一覧表示関数
    function loadRecipes(search = '', tagId = '', sortField = 'created_at', sortOrder = 'desc') {
        let url = 'api/recipes.php';
        let params = [];
        
        if(search) {
            params.push(`search=${encodeURIComponent(search)}`);
        }
        
        if(tagId) {
            params.push(`tag_id=${encodeURIComponent(tagId)}`);
        }
        
        if(sortField && sortOrder) {
            params.push(`sort_field=${encodeURIComponent(sortField)}`);
            params.push(`sort_order=${encodeURIComponent(sortOrder)}`);
        }
        
        if(params.length > 0) {
            url += '?' + params.join('&');
        }
        
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                // レシピ一覧を表示
                displayRecipes(response.records);
            },
            error: function(xhr, status, error) {
                alert('レシピデータの取得に失敗しました');
                console.error(xhr.responseText);
            }
        });
    }
    
    // レシピ一覧表示の更新
    function displayRecipes(recipes) {
        const container = $('#recipes-container');
        container.empty();
        
        if(recipes.length === 0) {
            container.html('<div class="col-12 text-center"><p>該当するレシピがありません</p></div>');
            return;
        }
        
        recipes.forEach(recipe => {
            // タグの表示を準備
            let tagsHtml = '';
            if(recipe.tags && recipe.tags.length > 0) {
                recipe.tags.forEach(tag => {
                    tagsHtml += `<span class="tag">${tag.name}</span>`;
                });
            }
            
            // 説明文の切り詰め
            const description = recipe.description ? 
                (recipe.description.length > 100 ? recipe.description.substring(0, 100) + '...' : recipe.description) 
                : '';
            
            // レシピカードの作成
            const card = `
                <div class="col-md-4 recipe-card">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">${recipe.recipe_name}</h5>
                            <p class="card-text"><small>調理時間: ${recipe.cook_time}分</small></p>
                            <p class="card-text">${description}</p>
                            <div class="tags-container mb-2">${tagsHtml}</div>
                            <button class="btn btn-primary btn-sm view-recipe" data-id="${recipe.recipe_id}">詳細</button>
                        </div>
                        <div class="card-footer text-muted">
                            作成日: ${formatDate(recipe.created_at)}
                        </div>
                    </div>
                </div>
            `;
            
            container.append(card);
        });
    }
    
    // タグ一覧の読み込み
    function loadTags() {
        $.ajax({
            url: 'api/tags.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                const tagSelect = $('#tag-filter');
                tagSelect.find('option:not(:first)').remove();
                
                if(response && response.length > 0) {
                    response.forEach(tag => {
                        tagSelect.append(`<option value="${tag.tag_id}">${tag.tag_name}</option>`);
                    });
                }
                
                const tagsContainer = $('#tags-container');
                tagsContainer.empty();
                
                if(response && response.length > 0) {
                    const row = $('<div class="row"></div>');
                    
                    response.forEach(tag => {
                        const checkboxDiv = `
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input tag-checkbox" type="checkbox" id="tag-${tag.tag_id}" value="${tag.tag_id}" data-tag-name="${tag.tag_name}">
                                    <label class="form-check-label" for="tag-${tag.tag_id}">
                                        ${tag.tag_name}
                                    </label>
                                </div>
                            </div>
                        `;
                        row.append(checkboxDiv);
                    });
                    
                    tagsContainer.append(row);
                    tagsContainer.append(`
                        <div class="mt-2 border-top pt-2">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> 新しいタグを追加するには、<a href="tag_register.html" target="_blank">タグ管理画面</a>をご利用ください。
                            </div>
                        </div>
                    `);
                } else {
                    tagsContainer.html(`
                        <p>タグがありません。<a href="tag_register.html" target="_blank">タグ管理画面</a>から新しいタグを追加してください。</p>
                    `);
                }
            },
            error: function(xhr, status, error) {
                console.error('タグの取得に失敗しました:', xhr.responseText);
                $('#tags-container').html('<p class="text-danger">タグ情報の読み込みに失敗しました。</p>');
            }
        });
    }
    
    // レシピ詳細表示
    function loadRecipeDetail(recipeId) {
        $.ajax({
            url: `api/recipes.php?id=${recipeId}`,
            type: 'GET',
            dataType: 'json',
            success: function(recipe) {
                $('#recipe-detail-title').text(recipe.recipe_name);
                
                let tagsHtml = '';
                if(recipe.tags && recipe.tags.length > 0) {
                    recipe.tags.forEach(tag => {
                        tagsHtml += `<span class="tag">${tag.name}</span>`;
                    });
                }
                
                const ingredients = recipe.ingredients.split('\n').map(item => `<li>${item}</li>`).join('');
                const steps = recipe.steps.split('\n').map((step, index) => `<p><strong>${index + 1}.</strong> ${step}</p>`).join('');
                
                const detailContent = `
                    <div class="mb-3">
                        <p><strong>調理時間:</strong> ${recipe.cook_time}分</p>
                        <div class="tags-container mb-2">${tagsHtml}</div>
                        <p><strong>説明:</strong> ${recipe.description || '説明なし'}</p>
                    </div>
                    <div class="mb-3">
                        <h5>材料</h5>
                        <ul>${ingredients}</ul>
                    </div>
                    <div class="mb-3">
                        <h5>手順</h5>
                        <div>${steps}</div>
                    </div>
                    <div class="text-muted small">
                        作成日: ${formatDate(recipe.created_at)}
                        ${recipe.updated_at ? '、更新日: ' + formatDate(recipe.updated_at) : ''}
                    </div>
                `;
                
                $('#recipe-detail-content').html(detailContent);
                
                $('.edit-recipe').data('id', recipe.recipe_id);
                $('.delete-recipe').data('id', recipe.recipe_id);
                currentRecipeId = recipe.recipe_id;
                
                $('#recipe-detail-modal').modal('show');
            },
            error: function(xhr, status, error) {
                alert('レシピ詳細の取得に失敗しました');
                console.error(xhr.responseText);
            }
        });
    }
    
    // レシピ編集（フォームに値を設定）
    function editRecipe(recipeId) {
        $.ajax({
            url: `api/recipes.php?id=${recipeId}`,
            type: 'GET',
            dataType: 'json',
            success: function(recipe) {
                $('#recipe-detail-modal').modal('hide');
                
                editMode = true;
                currentRecipeId = recipe.recipe_id;
                
                $('#recipe-id').val(recipe.recipe_id);
                $('#recipe-name').val(recipe.recipe_name);
                $('#cook-time').val(recipe.cook_time);
                $('#ingredients').val(recipe.ingredients);
                $('#description').val(recipe.description);
                $('#steps').val(recipe.steps);
                
                $('.tag-checkbox').prop('checked', false);
                
                if(recipe.tags && recipe.tags.length > 0) {
                    recipe.tags.forEach(tag => {
                        $(`#tag-${tag.id}`).prop('checked', true);
                    });
                }
                
                $('#app-tabs a[href="#recipe-form"]').tab('show');
            },
            error: function(xhr, status, error) {
                alert('レシピ情報の取得に失敗しました');
                console.error(xhr.responseText);
            }
        });
    }
    
    // レシピ削除処理
    function deleteRecipe(recipeId) {
        if(confirm('このレシピを削除してもよろしいですか？')) {
            $.ajax({
                url: 'api/delete_recipe.php',
                type: 'DELETE',
                contentType: 'application/json',
                data: JSON.stringify({ recipe_id: recipeId }),
                success: function(response) {
                    $('#recipe-detail-modal').modal('hide');
                    
                    loadRecipes();
                    
                    alert('レシピが削除されました');
                },
                error: function(xhr, status, error) {
                    alert('レシピの削除に失敗しました');
                    console.error(xhr.responseText);
                }
            });
        }
    }
    
    // フォームリセット
    function resetForm() {
        $('#recipe-form')[0].reset();
        $('#recipe-id').val('');
        $('.tag-checkbox').prop('checked', false);
        editMode = false;
        currentRecipeId = null;
    }
    
    // 日付フォーマット用ヘルパー関数
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleString('ja-JP', { 
            year: 'numeric', 
            month: 'numeric', 
            day: 'numeric', 
            hour: '2-digit', 
            minute: '2-digit' 
        });
    }
    
    // イベントハンドラ設定
    
    $('#search-form').on('submit', function(e) {
        e.preventDefault();
        const searchQuery = $('#search-input').val().trim();
        const tagId = $('#tag-filter').val();
        const sortValues = $('#sort-select').val().split('-');
        const sortField = sortValues[0];
        const sortOrder = sortValues[1];
        
        loadRecipes(searchQuery, tagId, sortField, sortOrder);
    });
    
    $('#tag-filter').on('change', function() {
        $('#search-form').submit();
    });
    
    $('#sort-select').on('change', function() {
        $('#search-form').submit();
    });
    
    $(document).on('click', '.view-recipe', function() {
        const recipeId = $(this).data('id');
        loadRecipeDetail(recipeId);
    });
    
    $(document).on('click', '.edit-recipe', function() {
        const recipeId = $(this).data('id');
        editRecipe(recipeId);
    });
    
    $(document).on('click', '.delete-recipe', function() {
        const recipeId = $(this).data('id');
        deleteRecipe(recipeId);
    });
    
    $('#recipe-form').on('submit', function(e) {
        e.preventDefault();
        
        const selectedTagIds = [];
        $('.tag-checkbox:checked').each(function() {
            selectedTagIds.push(parseInt($(this).val()));
        });
        
        const formData = {
            recipe_name: $('#recipe-name').val().trim(),
            cook_time: parseInt($('#cook-time').val()),
            ingredients: $('#ingredients').val().trim(),
            description: $('#description').val().trim(),
            steps: $('#steps').val().trim(),
            tags: selectedTagIds
        };
        
        if(editMode && currentRecipeId) {
            formData.recipe_id = currentRecipeId;
        }
        
        $.ajax({
            url: 'api/create_recipe.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(formData),
            success: function(response) {
                alert(editMode ? 'レシピを更新しました' : 'レシピを登録しました');
                
                resetForm();
                
                $('#app-tabs a[href="#recipes-list"]').tab('show');
                
                loadRecipes();
            },
            error: function(xhr, status, error) {
                alert('レシピの保存に失敗しました');
                console.error(xhr.responseText);
            }
        });
    });
    
    $('#reset-form').on('click', function() {
        resetForm();
    });
});
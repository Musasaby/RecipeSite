# Recipe App - XAMPP実行手順

## 前提条件
- XAMPPがインストールされていること

## セットアップ手順

1. **XAMPPコントロールパネルを起動する**
   - ApacheとMySQLのサービスを開始します。「Start」ボタンをクリックしてください。

2. **プロジェクトファイルの配置**
   - このRecipeAppフォルダ全体を `C:\xampp\htdocs\RecipeApp` にコピーしてください。
   - または、`C:\xampp\htdocs` にシンボリックリンクを作成することもできます：
     ```
     mklink /D "C:\xampp\htdocs\RecipeApp" "C:\D\WebServiceProjects\RecipeApp"
     ```

3. **データベースのセットアップ**
   - ブラウザで http://localhost/phpmyadmin/ にアクセス
   - 新しいデータベースを作成（config.phpで指定した名前を使用）
   - 必要なテーブルを作成（下記のSQLスクリプトを実行）

4. **アプリケーションにアクセス**
   - ブラウザで以下のURLにアクセス：
   - http://localhost/RecipeApp/recipeAppMain.php

## データベース構造（セットアップ用SQL）

```sql
CREATE TABLE IF NOT EXISTS Recipes (
  recipe_id INT AUTO_INCREMENT PRIMARY KEY,
  recipe_name VARCHAR(255) NOT NULL,
  cook_time INT,
  material TEXT,
  explanation TEXT,
  steps TEXT,
  sreated_at DATETIME,
  updated_at DATETIME
);

CREATE TABLE IF NOT EXISTS Tags (
  tag_id INT AUTO_INCREMENT PRIMARY KEY,
  tag_name VARCHAR(50) NOT NULL
);

CREATE TABLE IF NOT EXISTS RecipeTag (
  recipe_id INT,
  tag_id INT,
  PRIMARY KEY (recipe_id, tag_id),
  FOREIGN KEY (recipe_id) REFERENCES Recipes(recipe_id),
  FOREIGN KEY (tag_id) REFERENCES Tags(tag_id)
);
```

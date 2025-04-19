-- データベースの作成
CREATE DATABASE IF NOT EXISTS recipe_app DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- 作成したデータベースを使用
USE recipe_app;

create table IF NOT EXISTS Recipes(
    `recipe_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `recipe_name` VARCHAR(255) NOT NULL,
    `cook_time` INT NOT NULL,
    `material` VARCHAR(255) NOT NULL,
    `explanation` TEXT,
    `steps` TEXT,
    `created_at` DATETIME,
    `updated_at` DATETIME
);

create table IF NOT EXISTS Tags(
    `tag_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `tag_name` VARCHAR(255) NOT NULL
);

create table IF NOT EXISTS RecipeTag(
    `recipe_id` INT NOT NULL,
    `tag_id` INT NOT NULL, 
    PRIMARY KEY(recipe_id,tag_id), 
    FOREIGN KEY(recipe_id) REFERENCES Recipes(recipe_id), 
    FOREIGN KEY(tag_id) REFERENCES Tags(tag_id)
);

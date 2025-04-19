USE `test`;

-- 既存のテーブルが存在する場合は削除（開発用）
DROP TABLE IF EXISTS recipe_tags;
DROP TABLE IF EXISTS images;
DROP TABLE IF EXISTS recipes;
DROP TABLE IF EXISTS tags;

-- レシピテーブル：基本情報を格納
CREATE TABLE recipes (
    id INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    content TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 画像テーブル：レシピに関連する画像を格納
CREATE TABLE images (
    id INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    recipe_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    alt_text VARCHAR(255),
    is_main BOOLEAN DEFAULT FALSE,
    upload_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE,
    INDEX (recipe_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- タグテーブル：タグのマスターデータ
CREATE TABLE tags (
    id INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- レシピとタグの中間テーブル：多対多の関係を管理
CREATE TABLE recipe_tags (
    recipe_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (recipe_id, tag_id),
    FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE,
    INDEX (recipe_id),
    INDEX (tag_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- サンプルデータの挿入（オプション）
INSERT INTO recipes (title, content) VALUES
('トマトパスタ', 'トマトソースで作るシンプルなパスタのレシピです。'),
('チョコレートケーキ', '濃厚なチョコレートケーキの作り方です。');

INSERT INTO tags (name) VALUES
('パスタ'),
('デザート'),
('イタリアン'),
('甘い'),
('簡単');

INSERT INTO recipe_tags (recipe_id, tag_id) VALUES
(1, 1), -- トマトパスタ - パスタ
(1, 3), -- トマトパスタ - イタリアン
(1, 5), -- トマトパスタ - 簡単
(2, 2), -- チョコレートケーキ - デザート
(2, 4); -- チョコレートケーキ - 甘い
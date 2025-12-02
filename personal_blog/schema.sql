CREATE DATABASE personal_blog CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE personal_blog;

USE personal_blog;

CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    category ENUM('Tech Log', 'Mood Log') NOT NULL,
    content TEXT NOT NULL,
    image_path VARCHAR(255) NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO posts (title, category, content, image_path, created_at, updated_at)
VALUES
('Welcome', 'Tech Log', 'First technical note written in **Markdown**.', NULL, NOW(), NOW()),
('Hello Journal', 'Mood Log', 'Today I feel inspired to build a blog.', NULL, NOW(), NOW());
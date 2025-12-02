<?php
require_once __DIR__ . '/db.php';

function blog_config(): array
{
    static $config;
    if (!$config) {
        $config = require __DIR__ . '/config.php';
    }
    return $config;
}

function ensure_session(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function is_mysql(): bool
{
    $config = blog_config();
    return $config['storage'] === 'mysql';
}

function sanitize_text(string $text): string
{
    return htmlspecialchars(trim($text), ENT_QUOTES, 'UTF-8');
}

function data_file_path(): string
{
    return __DIR__ . '/data/posts.json';
}

function users_file_path(): string
{
    return __DIR__ . '/data/users.json';
}

function read_posts(): array
{
    if (is_mysql()) {
        return db_read_posts();
    }
    $file = data_file_path();
    if (!file_exists($file)) {
        file_put_contents($file, json_encode([]));
    }
    $json = file_get_contents($file);
    $posts = json_decode($json, true) ?: [];
    return $posts;
}

function read_users(): array
{
    $file = users_file_path();
    if (!file_exists($file)) {
        file_put_contents($file, json_encode([]));
    }
    $json = file_get_contents($file);
    return json_decode($json, true) ?: [];
}

function write_posts(array $posts): void
{
    if (is_mysql()) {
        return; // MySQL writes handled separately
    }
    $file = data_file_path();
    file_put_contents($file, json_encode(array_values($posts), JSON_PRETTY_PRINT));
}

function write_users(array $users): void
{
    $file = users_file_path();
    file_put_contents($file, json_encode(array_values($users), JSON_PRETTY_PRINT));
}

function db_read_posts(?string $category = null): array
{
    $pdo = db_get_connection();
    if ($category) {
        $stmt = $pdo->prepare('SELECT * FROM posts WHERE category = :category ORDER BY created_at DESC');
        $stmt->execute(['category' => $category]);
    } else {
        $stmt = $pdo->query('SELECT * FROM posts ORDER BY created_at DESC');
    }
    return $stmt->fetchAll();
}

function get_all_posts(?string $category = null): array
{
    if (is_mysql()) {
        return db_read_posts($category);
    }
    $posts = read_posts();
    if ($category) {
        $posts = array_filter($posts, fn($post) => $post['category'] === $category);
    }
    usort($posts, fn($a, $b) => strtotime($b['created_at']) <=> strtotime($a['created_at']));
    return $posts;
}

function find_post(int $id): ?array
{
    if (is_mysql()) {
        $pdo = db_get_connection();
        $stmt = $pdo->prepare('SELECT * FROM posts WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $post = $stmt->fetch();
        return $post ?: null;
    }
    $posts = read_posts();
    foreach ($posts as $post) {
        if ((int) $post['id'] === $id) {
            return $post;
        }
    }
    return null;
}

function find_user(string $username): ?array
{
    if (is_mysql()) {
        $pdo = db_get_connection();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = :username LIMIT 1');
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();
        return $user ?: null;
    }
    $users = read_users();
    foreach ($users as $user) {
        if ($user['username'] === $username) {
            return $user;
        }
    }
    return null;
}

function create_user(string $username, string $password): bool
{
    $username = trim($username);
    $hash = password_hash($password, PASSWORD_DEFAULT);
    if (is_mysql()) {
        $pdo = db_get_connection();
        $stmt = $pdo->prepare('INSERT INTO users (username, password_hash, created_at) VALUES (:username, :password_hash, NOW())');
        try {
            return $stmt->execute(['username' => $username, 'password_hash' => $hash]);
        } catch (PDOException $e) {
            return false;
        }
    }
    $users = read_users();
    foreach ($users as $user) {
        if ($user['username'] === $username) {
            return false;
        }
    }
    $users[] = [
        'id' => count($users) ? max(array_column($users, 'id')) + 1 : 1,
        'username' => $username,
        'password_hash' => $hash,
        'created_at' => date('Y-m-d H:i:s'),
    ];
    write_users($users);
    return true;
}

function handle_upload(array $file): ?string
{
    $config = blog_config();
    if (empty($file['name'])) {
        return null;
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    if ($file['size'] > $config['upload']['max_size']) {
        return null;
    }
    $mime = mime_content_type($file['tmp_name']);
    if (!in_array($mime, $config['upload']['allowed_types'], true)) {
        return null;
    }
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'jpg';
    $filename = uniqid('img_', true) . '.' . $ext;
    $target = rtrim($config['upload']['directory'], '/') . '/' . $filename;
    if (!is_dir($config['upload']['directory'])) {
        mkdir($config['upload']['directory'], 0755, true);
    }
    if (move_uploaded_file($file['tmp_name'], $target)) {
        return 'uploads/' . $filename;
    }
    return null;
}

function create_post(string $title, string $category, string $content, ?string $imagePath = null): int
{
    $timestamp = date('Y-m-d H:i:s');
    if (is_mysql()) {
        $pdo = db_get_connection();
        $stmt = $pdo->prepare('INSERT INTO posts (title, category, content, image_path, created_at, updated_at) VALUES (:title, :category, :content, :image_path, :created_at, :updated_at)');
        $stmt->execute([
            'title' => $title,
            'category' => $category,
            'content' => $content,
            'image_path' => $imagePath,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);
        return (int) $pdo->lastInsertId();
    }
    $posts = read_posts();
    $id = count($posts) ? max(array_column($posts, 'id')) + 1 : 1;
    $posts[] = [
        'id' => $id,
        'title' => $title,
        'category' => $category,
        'content' => $content,
        'image_path' => $imagePath,
        'created_at' => $timestamp,
        'updated_at' => $timestamp,
    ];
    write_posts($posts);
    return $id;
}

function update_post(int $id, string $title, string $category, string $content, ?string $imagePath = null): bool
{
    $timestamp = date('Y-m-d H:i:s');
    if (is_mysql()) {
        $pdo = db_get_connection();
        $stmt = $pdo->prepare('UPDATE posts SET title = :title, category = :category, content = :content, image_path = :image_path, updated_at = :updated_at WHERE id = :id');
        return $stmt->execute([
            'title' => $title,
            'category' => $category,
            'content' => $content,
            'image_path' => $imagePath,
            'updated_at' => $timestamp,
            'id' => $id,
        ]);
    }
    $posts = read_posts();
    foreach ($posts as &$post) {
        if ((int) $post['id'] === $id) {
            $post['title'] = $title;
            $post['category'] = $category;
            $post['content'] = $content;
            $post['updated_at'] = $timestamp;
            if ($imagePath !== null) {
                $post['image_path'] = $imagePath;
            }
            write_posts($posts);
            return true;
        }
    }
    return false;
}

function delete_post(int $id): bool
{
    if (is_mysql()) {
        $pdo = db_get_connection();
        $stmt = $pdo->prepare('DELETE FROM posts WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
    $posts = read_posts();
    $posts = array_filter($posts, fn($post) => (int) $post['id'] !== $id);
    write_posts($posts);
    return true;
}

function require_login(): void
{
    ensure_session();
    if (empty($_SESSION['user'])) {
        header('Location: login.php');
        exit;
    }
}

function is_logged_in(): bool
{
    ensure_session();
    return !empty($_SESSION['user']);
}

function login(string $username, string $password): bool
{
    ensure_session();
    $user = find_user($username);
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user'] = $user['username'];
        $_SESSION['user_id'] = $user['id'] ?? null;
        return true;
    }

    // Fallback to config admin for legacy setups
    $config = blog_config();
    if ($username === $config['admin']['username'] && password_verify($password, $config['admin']['password_hash'])) {
        $_SESSION['user'] = $username;
        $_SESSION['user_id'] = null;
        return true;
    }
    return false;
}

function logout(): void
{
    ensure_session();
    $_SESSION = [];
    session_destroy();
}
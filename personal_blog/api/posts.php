<?php
require_once __DIR__ . '/../functions.php';
$config = blog_config();
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? ($_GET['api_key'] ?? '');

if ($apiKey !== $config['api_key']) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

switch ($method) {
    case 'GET':
        $id = isset($_GET['id']) ? (int) $_GET['id'] : null;
        $category = isset($_GET['category']) ? sanitize_text($_GET['category']) : null;
        if ($id) {
            $post = find_post($id);
            if (!$post) {
                http_response_code(404);
                echo json_encode(['error' => 'Not found']);
                exit;
            }
            echo json_encode($post);
            exit;
        }
        $posts = get_all_posts($category ?: null);
        echo json_encode($posts);
        break;
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $title = sanitize_text($input['title'] ?? '');
        $category = sanitize_text($input['category'] ?? '');
        $content = $input['content'] ?? '';
        if (!$title || !$category || !$content) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing fields']);
            exit;
        }
        $id = create_post($title, $category, $content, $input['image_path'] ?? null);
        echo json_encode(['id' => $id]);
        break;
    case 'PUT':
    case 'PATCH':
        parse_str(file_get_contents('php://input'), $data);
        $id = isset($data['id']) ? (int) $data['id'] : 0;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing id']);
            exit;
        }
        $title = sanitize_text($data['title'] ?? '');
        $category = sanitize_text($data['category'] ?? '');
        $content = $data['content'] ?? '';
        $image = $data['image_path'] ?? null;
        if (update_post($id, $title, $category, $content, $image)) {
            echo json_encode(['status' => 'updated']);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Not found']);
        }
        break;
    case 'DELETE':
        parse_str(file_get_contents('php://input'), $data);
        $id = isset($data['id']) ? (int) $data['id'] : 0;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing id']);
            exit;
        }
        delete_post($id);
        echo json_encode(['status' => 'deleted']);
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}


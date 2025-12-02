<?php
require_once __DIR__ . '/../functions.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['code' => 405, 'msg' => 'Method not allowed']);
    exit;
}

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);
if (!is_array($data)) {
    $data = $_POST;
}

$username = sanitize_text($data['username'] ?? '');
$password = $data['password'] ?? '';

if ($username === '' || $password === '') {
    http_response_code(422);
    echo json_encode(['code' => 422, 'msg' => 'Username and password are required']);
    exit;
}

if (login($username, $password)) {
    echo json_encode(['code' => 200, 'msg' => 'Login successful']);
    exit;
}

http_response_code(401);

echo json_encode(['code' => 401, 'msg' => 'Invalid credentials']);
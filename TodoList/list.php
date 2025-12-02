
<?php
header('Content-Type: application/json; charset=utf-8');

// Connet SQLite（put the file into list.sqlite）
$db = new PDO('sqlite:' . __DIR__ . '/list.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// First run of automatic table creation
$db->exec("
    CREATE TABLE IF NOT EXISTS todos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        done INTEGER NOT NULL DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );
");

// If table is empty, insert default tasks
$count = $db->query("SELECT COUNT(*) FROM todos")->fetchColumn();
if ($count == 0) {
    $defaults = [
        'Go to the library',
        'Read a book',
        'Talk with family',
        'Go to the restaurant',
        'Buy groceries',
        'Finish homework'
    ];
    $stmt = $db->prepare("INSERT INTO todos (title, done) VALUES (?, 0)");
    foreach ($defaults as $title) {
        $stmt->execute([$title]);
    }
}

// Read action parameter
$action = "";
if (isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];
}

// 1) List all tasks
if ($action == 'list') {

    $result = $db->query("SELECT id, title, done FROM todos ORDER BY id DESC;");
    $rows = $result->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($rows);
}


// 2) Add a task
elseif ($action == 'add') {

    $body = json_decode(file_get_contents('php://input'), true);
    $title = "";
    if (isset($body['title'])) {
        $title = trim($body['title']);
    }

    if ($title == "") {
        echo json_encode(['error' => 'Title cannot be empty']);
        exit;
    }

    $stmt = $db->prepare("INSERT INTO todos (title, done) VALUES (?, 0);");
    $stmt->execute([$title]);
    $id = $db->lastInsertId();
    echo json_encode([
        'id' => (int)$id,
        'title' => $title,
        'done' => 0
    ]);
}

// 3) Delete a task
elseif ($action == 'delete') {

    $id = 0;
    if (isset($_REQUEST['id'])) {
        $id = (int)$_REQUEST['id'];
    }
    $stmt = $db->prepare("DELETE FROM todos WHERE id = ?;");
    $stmt->execute([$id]);
    echo json_encode(['ok' => true]);
}


// 4) Toggle task completion status
elseif ($action == 'toggle') {

    $id = 0;
    if (isset($_REQUEST['id'])) {
        $id = (int)$_REQUEST['id'];
    }

    $stmt = $db->prepare("UPDATE todos SET done = 1 - done WHERE id = ?;");
    $stmt->execute([$id]);

    echo json_encode(['ok' => true]);
}

// 5) Update task title
elseif ($action == 'update') {

    $id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;

    $body = json_decode(file_get_contents('php://input'), true);
    $title = trim($body['title'] ?? '');

    if ($title == '') {
        echo json_encode(['error' => 'Title cannot be empty']);
        exit;
    }

    $stmt = $db->prepare("UPDATE todos SET title = ? WHERE id = ?;");
    $stmt->execute([$title, $id]);

    echo json_encode(['ok' => true]);
}

// 6) Unknown action
else {
    echo json_encode(['error' => 'Unknown action']);
}

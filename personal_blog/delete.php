<?php
require_once __DIR__ . '/functions.php';
require_login();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id) {
        delete_post($id);
    }
}
header('Location: index.php');
exit;
<?php
require_once __DIR__ . '/functions.php';
require_login();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize_text($_POST['title'] ?? '');
    $category = sanitize_text($_POST['category'] ?? '');
    $content = $_POST['content'] ?? '';
    $imagePath = null;
    if (!empty($_FILES['image'])) {
        $imagePath = handle_upload($_FILES['image']);
    }

    if ($title && $category && $content) {
        $id = create_post($title, $category, $content, $imagePath);
        header('Location: view.php?id=' . $id);
        exit;
    } else {
        $message = 'All fields are required.';
    }
}
include __DIR__ . '/templates/header.php';
?>
<section class="card">
    <h1>New Post</h1>
    <?php if ($message): ?><p class="meta danger"><?php echo $message; ?></p><?php endif; ?>
    <form action="" method="POST" enctype="multipart/form-data">
        <label for="title">Title</label>
        <input type="text" id="title" name="title" required>

        <label for="category">Category</label>
        <select id="category" name="category" required>
            <option value="Tech Log">Tech Log</option>
            <option value="Mood Log">Mood Log</option>
        </select>

        <label for="content">Content (Markdown supported)</label>
        <textarea id="content" name="content" required></textarea>

        <label for="image">Image Upload (optional)</label>
        <input type="file" id="image" name="image" accept="image/*">

        <div class="preview" id="preview"></div>

        <input type="submit" value="Publish">
    </form>
</section>
<?php include __DIR__ . '/templates/footer.php'; ?>
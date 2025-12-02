<?php
require_once __DIR__ . '/functions.php';
require_login();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$post = find_post($id);
if (!$post) {
    header('Location: index.php');
    exit;
}
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize_text($_POST['title'] ?? '');
    $category = sanitize_text($_POST['category'] ?? '');
    $content = $_POST['content'] ?? '';
    $imagePath = $post['image_path'] ?? null;
    if (!empty($_FILES['image']['name'])) {
        $uploaded = handle_upload($_FILES['image']);
        if ($uploaded) {
            $imagePath = $uploaded;
        }
    }
    if ($title && $category && $content) {
        update_post($id, $title, $category, $content, $imagePath);
        header('Location: view.php?id=' . $id);
        exit;
    } else {
        $message = 'All fields are required.';
    }
}
include __DIR__ . '/templates/header.php';
?>
<section class="card">
    <h1>Edit Post</h1>
    <?php if ($message): ?><p class="meta danger"><?php echo $message; ?></p><?php endif; ?>
    <form action="" method="POST" enctype="multipart/form-data">
        <label for="title">Title</label>
        <input type="text" id="title" name="title" value="<?php echo sanitize_text($post['title']); ?>" required>

        <label for="category">Category</label>
        <select id="category" name="category" required>
            <option value="Tech Log" <?php if ($post['category']==='Tech Log') echo 'selected'; ?>>Tech Log</option>
            <option value="Mood Log" <?php if ($post['category']==='Mood Log') echo 'selected'; ?>>Mood Log</option>
        </select>

        <label for="content">Content (Markdown supported)</label>
        <textarea id="content" name="content" required><?php echo htmlspecialchars($post['content']); ?></textarea>

        <label for="image">Image Upload (optional)</label>
        <input type="file" id="image" name="image" accept="image/*">
        <?php if (!empty($post['image_path'])): ?>
            <p class="meta">Current: <a href="/<?php echo sanitize_text($post['image_path']); ?>" target="_blank">View image</a></p>
        <?php endif; ?>

        <div class="preview" id="preview"></div>

        <input type="submit" value="Save Changes">
    </form>
</section>
<?php include __DIR__ . '/templates/footer.php'; ?>
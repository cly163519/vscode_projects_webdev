<?php
require_once __DIR__ . '/functions.php';
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$post = find_post($id);
if (!$post) {
    header('Location: index.php');
    exit;
}
include __DIR__ . '/templates/header.php';
?>
<article class="card">
    <div class="meta">
        <span class="badge"><?php echo sanitize_text($post['category']); ?></span>
        <span>• <?php echo date('M d, Y H:i', strtotime($post['created_at'])); ?></span>
    </div>
    <h1><?php echo sanitize_text($post['title']); ?></h1>
    <?php if (!empty($post['image_path'])): ?>
        <img class="responsive" src="/<?php echo sanitize_text($post['image_path']); ?>" alt="cover">
    <?php endif; ?>
    <div class="meta">Last updated: <?php echo date('M d, Y H:i', strtotime($post['updated_at'])); ?></div>
    <div data-markdown><?php echo htmlspecialchars($post['content']); ?></div>
    <?php if (is_logged_in()): ?>
        <div class="flex between center" style="margin-top:12px;">
            <a class="button secondary" href="/edit.php?id=<?php echo $post['id']; ?>">Edit</a>
            <form action="/delete.php" method="POST" onsubmit="return confirm('Delete this post?');">
                <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
                <button class="danger" type="submit">Delete</button>
            </form>
        </div>
    <?php endif; ?>
</article>
<?php include __DIR__ . '/templates/footer.php'; ?>
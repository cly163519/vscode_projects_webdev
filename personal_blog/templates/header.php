<?php
$config = blog_config();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $config['site_name']; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dompurify@3.0.9/dist/purify.min.js"></script>
    <script defer src="/assets/js/main.js"></script>
</head>
<body>
    <header class="topbar">
        <div class="container navbar">
            <div class="logo">My Personal Blog</div>
            <nav>
                <a href="/personal_blog/index.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>">Home</a>
                <a href="/personal_blog/create.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'create.php' ? 'active' : ''; ?>">New Post</a>
                <a href="/personal_blog/index.php?category=Tech%20Log">Tech Logs</a>
                <a href="/personal_blog/index.php?category=Mood%20Log">Mood Logs</a>
                <?php if (is_logged_in()): ?>
                    <a href="/personal_blog/logout.php">Logout</a>
                <?php else: ?>
                    <a href="/personal_blog/login.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'login.php' ? 'active' : ''; ?>">Login</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    <main class="container">
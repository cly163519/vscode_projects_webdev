<?php
require_once __DIR__ . '/functions.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_text($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username and password are required.';
    } elseif (login($username, $password)) {
        header('Location: index.php');
        exit;
    } else {
        $error = 'Invalid credentials.';
    }
}

include __DIR__ . '/templates/header.php';
?>
<section class="card">
    <h1>Login</h1>
    <p class="meta">Already signed up? Enter your credentials below.</p>
    <?php if ($error): ?>
        <p class="meta danger"><?php echo $error; ?></p>
    <?php endif; ?>
    <form method="post" action="login.php">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required autocomplete="username" value="<?php echo htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES); ?>">

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required autocomplete="current-password">

        <button type="submit">Login</button>
    </form>
    <p class="meta">New here? <a href="signup.php">Create an account</a> first.</p>
</section>
<?php include __DIR__ . '/templates/footer.php'; ?>
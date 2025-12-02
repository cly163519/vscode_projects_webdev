<?php
require_once __DIR__ . '/functions.php';

$error = '';
$username = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_text($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if ($username === '' || $password === '' || $confirm === '') {
        $error = 'Username, password, and confirmation are required.';
    } elseif (!preg_match('/^[A-Za-z0-9_\-]{3,32}$/', $username)) {
        $error = 'Username must be 3-32 characters (letters, numbers, underscores, dashes).';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (find_user($username)) {
        $error = 'Username already exists. Please choose another.';
    } elseif (create_user($username, $password)) {
        login($username, $password);
        header('Location: index.php');
        exit;
    } else {
        $error = 'Could not create account. Please try again.';
    }
}

include __DIR__ . '/templates/header.php';
?>
<section class="card">
    <h1>Sign Up</h1>
    <p class="meta">Create an account to start posting.</p>
    <?php if ($error): ?>
        <p class="meta danger"><?php echo $error; ?></p>
    <?php endif; ?>
    <form method="post" action="signup.php" novalidate>
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required minlength="3" maxlength="32" pattern="[A-Za-z0-9_\-]+" value="<?php echo htmlspecialchars($username, ENT_QUOTES); ?>" autocomplete="username">

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required minlength="6" autocomplete="new-password">

        <label for="confirm">Confirm Password</label>
        <input type="password" id="confirm" name="confirm" required minlength="6" autocomplete="new-password">

        <button type="submit">Create Account</button>
    </form>
    <p class="meta">Already registered? <a href="login.php">Login here</a>.</p>
</section>
<?php include __DIR__ . '/templates/footer.php'; ?>
<?php
require __DIR__ . '/includes/helpers.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!csrf_valid($_POST['csrf'] ?? null)) {
        $errors['form'] = 'Invalid form submission. Please try again.';
    }

    if ($email === '') {
        $errors['email'] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    if ($password === '') {
        $errors['password'] = 'Password is required.';
    }

    if (empty($errors)) {
        $key = strtolower($email);
        $users = sample_users() + ($_SESSION['registered_users'] ?? []);

        if (isset($users[$key]) && password_verify($password, $users[$key]['password_hash'])) {
            session_regenerate_id(true); // prevents session fixation
            $_SESSION['user'] = ['name' => $users[$key]['name'], 'email' => $key];
            set_flash('success', 'Login successful. Welcome back!');
            redirect('dashboard.php');
        }

        // Same message for wrong email or wrong password (do not reveal which)
        $errors['form'] = 'Invalid email or password.';
    }
}

$pageTitle = 'Login';
require __DIR__ . '/includes/header.php';
?>
<h1>Login</h1>

<?php if (isset($errors['form'])): ?>
    <div class="alert error"><?= e($errors['form']) ?></div>
<?php endif; ?>

<form method="POST" action="login.php" novalidate>
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

    <label for="email">Email</label>
    <input type="email" id="email" name="email" value="<?= e($email) ?>">
    <?php if (isset($errors['email'])): ?><span class="error-text"><?= e($errors['email']) ?></span><?php endif; ?>

    <label for="password">Password</label>
    <input type="password" id="password" name="password" autocomplete="current-password">
    <?php if (isset($errors['password'])): ?><span class="error-text"><?= e($errors['password']) ?></span><?php endif; ?>

    <button type="submit">Login</button>
</form>
<p class="link">No account yet? <a href="register.php">Register</a></p>
<p class="hint">Sample login: ali@example.com / Password@123</p>
<?php require __DIR__ . '/includes/footer.php'; ?>

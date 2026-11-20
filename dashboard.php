<?php
require __DIR__ . '/includes/helpers.php';

if (!is_logged_in()) {
    set_flash('error', 'Please log in to view that page.');
    redirect('login.php');
}

$user = $_SESSION['user'];
$pageTitle = 'Dashboard';
require __DIR__ . '/includes/header.php';
?>
<h1>Welcome, <?= e($user['name']) ?>!</h1>
<p>You are logged in as <strong><?= e($user['email']) ?></strong>.</p>
<a class="button" href="logout.php">Logout</a>
<?php require __DIR__ . '/includes/footer.php'; ?>

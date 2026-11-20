<?php
require __DIR__ . '/includes/helpers.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

$errors = [];
$old = ['name' => '', 'email' => '', 'phone' => '', 'gender' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Read and clean the input
    $old['name']   = trim($_POST['name'] ?? '');
    $old['email']  = trim($_POST['email'] ?? '');
    $old['phone']  = trim($_POST['phone'] ?? '');
    $old['gender'] = $_POST['gender'] ?? '';
    $password      = $_POST['password'] ?? '';
    $confirm       = $_POST['confirm_password'] ?? '';

    if (!csrf_valid($_POST['csrf'] ?? null)) {
        $errors['form'] = 'Invalid form submission. Please try again.';
    }

    // 2. Full name
    if ($old['name'] === '') {
        $errors['name'] = 'Full name is required.';
    } elseif (strlen($old['name']) < 3) {
        $errors['name'] = 'Full name must be at least 3 characters.';
    } elseif (!preg_match("/^[\p{L} .'-]+$/u", $old['name'])) {
        $errors['name'] = 'Full name can only contain letters and spaces.';
    }

    // 3. Email
    if ($old['email'] === '') {
        $errors['email'] = 'Email is required.';
    } elseif (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    } elseif (
        isset(sample_users()[strtolower($old['email'])]) ||
        isset($_SESSION['registered_users'][strtolower($old['email'])])
    ) {
        $errors['email'] = 'This email is already registered.';
    }

    // 4. Phone
    $phoneDigits = preg_replace('/[\s-]/', '', $old['phone']);
    if ($old['phone'] === '') {
        $errors['phone'] = 'Phone number is required.';
    } elseif (!preg_match('/^\+?[0-9]{10,15}$/', $phoneDigits)) {
        $errors['phone'] = 'Enter a valid phone number (10 to 15 digits, e.g. 03001234567).';
    }

    // 5. Gender
    if ($old['gender'] === '') {
        $errors['gender'] = 'Please select your gender.';
    } elseif (!in_array($old['gender'], ['Male', 'Female', 'Other'], true)) {
        $errors['gender'] = 'Invalid gender selected.';
    }

    // 6. Password
    if ($password === '') {
        $errors['password'] = 'Password is required.';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters.';
    } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $errors['password'] = 'Password must have an uppercase letter, a lowercase letter, and a number.';
    }

    // 7. Confirm password
    if ($confirm === '') {
        $errors['confirm_password'] = 'Please confirm your password.';
    } elseif ($password !== $confirm) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }

    // 8. No errors: save (in session only, no database) and redirect
    if (empty($errors)) {
        $_SESSION['registered_users'][strtolower($old['email'])] = [
            'name'          => $old['name'],
            'password_hash' => password_hash($password, PASSWORD_DEFAULT), // never store plain text
        ];
        set_flash('success', 'Registration successful! You can now log in.');
        redirect('login.php');
    }
}

$pageTitle = 'Register';
require __DIR__ . '/includes/header.php';
?>
<h1>Create Account</h1>

<?php if (!empty($errors)): ?>
    <div class="alert error">Please fix the errors below and try again.</div>
<?php endif; ?>
<?php if (isset($errors['form'])): ?>
    <div class="alert error"><?= e($errors['form']) ?></div>
<?php endif; ?>

<form method="POST" action="register.php" novalidate>
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

    <label for="name">Full Name</label>
    <input type="text" id="name" name="name" value="<?= e($old['name']) ?>">
    <?php if (isset($errors['name'])): ?><span class="error-text"><?= e($errors['name']) ?></span><?php endif; ?>

    <label for="email">Email</label>
    <input type="email" id="email" name="email" value="<?= e($old['email']) ?>">
    <?php if (isset($errors['email'])): ?><span class="error-text"><?= e($errors['email']) ?></span><?php endif; ?>

    <label for="phone">Phone Number</label>
    <input type="tel" id="phone" name="phone" value="<?= e($old['phone']) ?>">
    <?php if (isset($errors['phone'])): ?><span class="error-text"><?= e($errors['phone']) ?></span><?php endif; ?>

    <label>Gender</label>
    <div class="radios">
        <?php foreach (['Male', 'Female', 'Other'] as $g): ?>
            <label class="radio">
                <input type="radio" name="gender" value="<?= e($g) ?>" <?= $old['gender'] === $g ? 'checked' : '' ?>> <?= e($g) ?>
            </label>
        <?php endforeach; ?>
    </div>
    <?php if (isset($errors['gender'])): ?><span class="error-text"><?= e($errors['gender']) ?></span><?php endif; ?>

    <!-- Password fields are never filled back in -->
    <label for="password">Password</label>
    <input type="password" id="password" name="password" autocomplete="new-password">
    <?php if (isset($errors['password'])): ?><span class="error-text"><?= e($errors['password']) ?></span><?php endif; ?>

    <label for="confirm_password">Confirm Password</label>
    <input type="password" id="confirm_password" name="confirm_password" autocomplete="new-password">
    <?php if (isset($errors['confirm_password'])): ?><span class="error-text"><?= e($errors['confirm_password']) ?></span><?php endif; ?>

    <button type="submit">Register</button>
</form>
<p class="link">Already have an account? <a href="login.php">Log in</a></p>
<?php require __DIR__ . '/includes/footer.php'; ?>

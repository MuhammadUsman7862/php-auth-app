<?php
require __DIR__ . '/includes/helpers.php';

$registered = $_SESSION['registered_users'] ?? [];
$_SESSION = [];
session_destroy();

// Start a fresh session just to show the logout message
session_start();
$_SESSION['registered_users'] = $registered;
set_flash('success', 'You have been logged out.');
redirect('login.php');

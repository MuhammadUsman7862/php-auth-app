<?php
require __DIR__ . '/includes/helpers.php';
redirect(is_logged_in() ? 'dashboard.php' : 'login.php');

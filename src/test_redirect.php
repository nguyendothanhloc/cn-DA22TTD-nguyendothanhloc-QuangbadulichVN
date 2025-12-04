<?php
// Test file to check redirect logic
session_start();

// Simulate being in user directory
$_SERVER['SCRIPT_NAME'] = '/dulich/user/home.php';

// Get the base directory of the application
$script_name = $_SERVER['SCRIPT_NAME'];
echo "Script name: $script_name<br>";

$base_dir = dirname($script_name);
echo "Base dir before: $base_dir<br>";

// Remove /admin or /user from the path if present
$base_dir = preg_replace('#/(admin|user)$#', '', $base_dir);
echo "Base dir after: $base_dir<br>";

// Build the login URL
$login_url = $base_dir . '/login.php';
echo "Login URL: $login_url<br>";
?>

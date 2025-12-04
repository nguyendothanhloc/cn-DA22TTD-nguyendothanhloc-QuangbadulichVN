<?php
session_start();
require_once '../auth.php';

// Require admin role
requireRole('admin');

// Redirect to places page
header('Location: places.php');
exit;
?>
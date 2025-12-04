<?php
/**
 * Authentication and Authorization Module
 * Handles user authentication and role-based access control
 */

/**
 * Check if user is authenticated
 * @return bool True if user is logged in, false otherwise
 */
function checkAuth() {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Check if user_id exists in session
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user has required role
 * @param string $required_role The role required to access the resource ('admin' or 'user')
 * @return bool True if user has the required role, false otherwise
 */
function checkRole($required_role) {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Check if user is authenticated first
    if (!checkAuth()) {
        return false;
    }
    
    // Check if user has the required role
    return isset($_SESSION['role']) && $_SESSION['role'] === $required_role;
}

/**
 * Redirect to login page
 * @param string $message Optional message to display on login page
 */
function redirectToLogin($message = '') {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Store message in session if provided
    if (!empty($message)) {
        $_SESSION['login_message'] = $message;
    }
    
    // Determine login path based on current directory
    $current_file = $_SERVER['SCRIPT_NAME'];
    
    // Check if we're in admin or user subdirectory
    if (strpos($current_file, '/admin/') !== false || strpos($current_file, '/user/') !== false) {
        // We're in a subdirectory, go up one level
        $login_url = '../login.php';
    } else {
        // We're in root directory
        $login_url = 'login.php';
    }
    
    // Redirect to login page
    header('Location: ' . $login_url);
    exit;
}

/**
 * Require authentication - redirect to login if not authenticated
 */
function requireAuth() {
    if (!checkAuth()) {
        redirectToLogin('Vui lòng đăng nhập để tiếp tục');
    }
}

/**
 * Require specific role - redirect or show error if user doesn't have required role
 * @param string $required_role The role required ('admin' or 'user')
 */
function requireRole($required_role) {
    // First check if user is authenticated
    if (!checkAuth()) {
        redirectToLogin('Vui lòng đăng nhập để tiếp tục');
    }
    
    // Then check if user has required role
    if (!checkRole($required_role)) {
        die('Bạn không có quyền truy cập trang này');
    }
}
?>

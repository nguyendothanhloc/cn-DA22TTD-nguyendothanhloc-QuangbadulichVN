<?php
/**
 * Session Configuration and Management
 * Handles session initialization and validation
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'use_strict_mode' => true,
        'cookie_lifetime' => 0, // Session cookie expires when browser closes
        'gc_maxlifetime' => 3600 // Session data expires after 1 hour
    ]);
}

// Set session timeout (1 hour = 3600 seconds)
$session_timeout = 3600;

// Check if session has timed out
if (isset($_SESSION['last_activity'])) {
    $elapsed_time = time() - $_SESSION['last_activity'];
    
    if ($elapsed_time > $session_timeout) {
        // Session has expired, clear it
        session_unset();
        session_destroy();
        
        // Start a new session
        session_start([
            'cookie_httponly' => true,
            'use_strict_mode' => true,
            'cookie_lifetime' => 0,
            'gc_maxlifetime' => 3600
        ]);
        
        // Set message for user
        $_SESSION['login_message'] = 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.';
    }
}

// Update last activity timestamp
$_SESSION['last_activity'] = time();

// Regenerate session ID periodically (every 30 minutes) to prevent session fixation
if (!isset($_SESSION['created'])) {
    $_SESSION['created'] = time();
} else if (time() - $_SESSION['created'] > 1800) {
    // Session was created more than 30 minutes ago
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}
?>

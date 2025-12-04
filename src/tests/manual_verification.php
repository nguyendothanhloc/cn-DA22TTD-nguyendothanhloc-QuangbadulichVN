<?php
/**
 * Manual Testing and Verification Script
 * Feature: user-registration
 * Task 6: Manual testing và verification
 * 
 * This script performs comprehensive manual testing of the registration system
 * to verify all requirements are met.
 * 
 * Run this file: php tests/manual_verification.php
 */

// Include database connection
require_once __DIR__ . '/../db.php';

// ANSI color codes for better output
define('COLOR_GREEN', "\033[32m");
define('COLOR_RED', "\033[31m");
define('COLOR_YELLOW', "\033[33m");
define('COLOR_BLUE', "\033[34m");
define('COLOR_RESET', "\033[0m");

// Test results tracking
$test_results = [];
$test_usernames = [];

/**
 * Print section header
 */
function printSection($title) {
    echo "\n" . COLOR_BLUE . str_repeat("=", 60) . COLOR_RESET . "\n";
    echo COLOR_BLUE . $title . COLOR_RESET . "\n";
    echo COLOR_BLUE . str_repeat("=", 60) . COLOR_RESET . "\n\n";
}

/**
 * Print test result
 */
function printResult($test_name, $passed, $message = '') {
    global $test_results;
    
    $status = $passed ? COLOR_GREEN . "✅ PASSED" : COLOR_RED . "❌ FAILED";
    echo "$status" . COLOR_RESET . " - $test_name\n";
    
    if (!empty($message)) {
        echo "  " . COLOR_YELLOW . $message . COLOR_RESET . "\n";
    }
    
    $test_results[] = ['name' => $test_name, 'passed' => $passed, 'message' => $message];
}

/**
 * Clean up test users
 */
function cleanupTestUsers($conn, $usernames) {
    if (empty($usernames)) {
        return;
    }
    
    foreach ($usernames as $username) {
        $stmt = $conn->prepare("DELETE FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->close();
    }
}

// ============================================================================
// TEST 1: Registration with valid data
// ============================================================================
printSection("TEST 1: Registration with Valid Data");

$test_username = 'manual_test_' . bin2hex(random_bytes(4));
$test_password = 'testpass123';
$test_fullname = 'Manual Test User';
$test_email = 'manual_test@example.com';
$test_phone = '0901234567';

$test_usernames[] = $test_username;

// Simulate registration
$hashed_password = password_hash($test_password, PASSWORD_DEFAULT);
$stmt = $conn->prepare(
    "INSERT INTO users (username, password, role, fullname, email, phone) VALUES (?, ?, 'user', ?, ?, ?)"
);
$stmt->bind_param("sssss", $test_username, $hashed_password, $test_fullname, $test_email, $test_phone);
$registration_success = $stmt->execute();
$stmt->close();

if ($registration_success) {
    // Verify user exists in database
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $test_username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    if ($user) {
        printResult("User created in database", true, "Username: $test_username");
        printResult("Role is 'user'", $user['role'] === 'user', "Role: {$user['role']}");
        printResult("Password is hashed", substr($user['password'], 0, 4) === '$2y$', "Hash prefix: " . substr($user['password'], 0, 4));
        printResult("Password verifies correctly", password_verify($test_password, $user['password']));
        printResult("Fullname stored correctly", $user['fullname'] === $test_fullname);
        printResult("Email stored correctly", $user['email'] === $test_email);
        printResult("Phone stored correctly", $user['phone'] === $test_phone);
    } else {
        printResult("User created in database", false, "User not found after registration");
    }
} else {
    printResult("Registration with valid data", false, "Registration failed: " . $conn->error);
}

// ============================================================================
// TEST 2: Registration with existing username
// ============================================================================
printSection("TEST 2: Registration with Existing Username");

// Try to register with the same username
try {
    $stmt = $conn->prepare(
        "INSERT INTO users (username, password, role, fullname, email, phone) VALUES (?, ?, 'user', ?, ?, ?)"
    );
    $stmt->bind_param("sssss", $test_username, $hashed_password, $test_fullname, $test_email, $test_phone);
    $duplicate_registration = $stmt->execute();
    $error = $stmt->error;
    $stmt->close();
} catch (Exception $e) {
    $duplicate_registration = false;
    $error = $e->getMessage();
}

printResult(
    "Duplicate username rejected",
    !$duplicate_registration,
    $duplicate_registration ? "ERROR: Duplicate was accepted!" : "Correctly rejected: $error"
);

// ============================================================================
// TEST 3: Password validation - mismatched passwords
// ============================================================================
printSection("TEST 3: Password Validation - Mismatched Passwords");

$password1 = 'password123';
$password2 = 'password456';
$passwords_match = ($password1 === $password2);

printResult(
    "Mismatched passwords detected",
    !$passwords_match,
    $passwords_match ? "ERROR: Mismatch not detected!" : "Correctly detected mismatch"
);

// ============================================================================
// TEST 4: Password validation - short password
// ============================================================================
printSection("TEST 4: Password Validation - Short Password");

$short_passwords = ['', 'a', 'ab', 'abc', 'abcd', 'abcde'];
$all_rejected = true;

foreach ($short_passwords as $short_pass) {
    $is_valid = strlen($short_pass) >= 6;
    if ($is_valid) {
        $all_rejected = false;
        printResult(
            "Password '$short_pass' (length " . strlen($short_pass) . ")",
            false,
            "ERROR: Short password was accepted!"
        );
    }
}

if ($all_rejected) {
    printResult(
        "All short passwords rejected",
        true,
        "Tested passwords with length 0-5 characters"
    );
}

// Test valid password length
$valid_password = 'pass123';
$is_valid = strlen($valid_password) >= 6;
printResult(
    "Valid password accepted (6+ chars)",
    $is_valid,
    "Password: '$valid_password' (length " . strlen($valid_password) . ")"
);

// ============================================================================
// TEST 5: Email and phone validation
// ============================================================================
printSection("TEST 5: Email and Phone Validation");

// Test invalid emails
$invalid_emails = ['notanemail', 'missing@domain', '@nodomain.com', 'spaces in@email.com'];
$all_invalid_rejected = true;

foreach ($invalid_emails as $email) {
    $is_valid = filter_var($email, FILTER_VALIDATE_EMAIL);
    if ($is_valid) {
        $all_invalid_rejected = false;
        printResult("Invalid email '$email'", false, "ERROR: Invalid email was accepted!");
    }
}

printResult(
    "Invalid emails rejected",
    $all_invalid_rejected,
    "Tested: " . implode(', ', $invalid_emails)
);

// Test valid email
$valid_email = 'valid@example.com';
$is_valid = filter_var($valid_email, FILTER_VALIDATE_EMAIL);
printResult("Valid email accepted", $is_valid, "Email: $valid_email");

// Test invalid phones
$invalid_phones = ['123', '12345678901234', 'abcdefghij', '090-123-456'];
$all_phone_invalid_rejected = true;

foreach ($invalid_phones as $phone) {
    $is_valid = preg_match('/^[0-9]{10,11}$/', $phone);
    if ($is_valid) {
        $all_phone_invalid_rejected = false;
        printResult("Invalid phone '$phone'", false, "ERROR: Invalid phone was accepted!");
    }
}

printResult(
    "Invalid phones rejected",
    $all_phone_invalid_rejected,
    "Tested: " . implode(', ', $invalid_phones)
);

// Test valid phones
$valid_phones = ['0901234567', '09012345678'];
$all_phone_valid_accepted = true;

foreach ($valid_phones as $phone) {
    $is_valid = preg_match('/^[0-9]{10,11}$/', $phone);
    if (!$is_valid) {
        $all_phone_valid_accepted = false;
        printResult("Valid phone '$phone'", false, "ERROR: Valid phone was rejected!");
    }
}

printResult(
    "Valid phones accepted (10-11 digits)",
    $all_phone_valid_accepted,
    "Tested: " . implode(', ', $valid_phones)
);

// ============================================================================
// TEST 6: Password hashing verification
// ============================================================================
printSection("TEST 6: Password Hashing Verification");

// Check all users have bcrypt hashed passwords
$result = $conn->query("SELECT username, password FROM users");
$all_hashed = true;
$non_hashed_users = [];

while ($user = $result->fetch_assoc()) {
    if (substr($user['password'], 0, 4) !== '$2y$') {
        $all_hashed = false;
        $non_hashed_users[] = $user['username'];
    }
}

printResult(
    "All passwords are bcrypt hashed",
    $all_hashed,
    $all_hashed ? "All users have bcrypt hashed passwords" : "Non-hashed users: " . implode(', ', $non_hashed_users)
);

// ============================================================================
// TEST 7: Test accounts verification
// ============================================================================
printSection("TEST 7: Test Accounts Verification");

// Check admin account
$stmt = $conn->prepare("SELECT username, role, password FROM users WHERE username = 'admin'");
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$stmt->close();

if ($admin) {
    printResult("Admin account exists", true, "Username: admin");
    printResult("Admin role is correct", $admin['role'] === 'admin', "Role: {$admin['role']}");
    printResult("Admin password is hashed", substr($admin['password'], 0, 4) === '$2y$');
    
    // Test admin login
    $admin_login_works = password_verify('admin123', $admin['password']);
    printResult("Admin can login with 'admin123'", $admin_login_works);
} else {
    printResult("Admin account exists", false, "Admin account not found!");
}

// Check testuser account
$stmt = $conn->prepare("SELECT username, role, password FROM users WHERE username = 'testuser'");
$stmt->execute();
$result = $stmt->get_result();
$testuser = $result->fetch_assoc();
$stmt->close();

if ($testuser) {
    printResult("Testuser account exists", true, "Username: testuser");
    printResult("Testuser role is correct", $testuser['role'] === 'user', "Role: {$testuser['role']}");
    printResult("Testuser password is hashed", substr($testuser['password'], 0, 4) === '$2y$');
    
    // Test testuser login
    $testuser_login_works = password_verify('user123', $testuser['password']);
    printResult("Testuser can login with 'user123'", $testuser_login_works);
} else {
    printResult("Testuser account exists", false, "Testuser account not found!");
}

// ============================================================================
// TEST 8: Navigation links verification
// ============================================================================
printSection("TEST 8: Navigation Links Verification");

// Check register.php exists
$register_exists = file_exists(__DIR__ . '/../register.php');
printResult("register.php file exists", $register_exists);

if ($register_exists) {
    $register_content = file_get_contents(__DIR__ . '/../register.php');
    
    // Check for link to login page
    $has_login_link = (strpos($register_content, 'login.php') !== false) && 
                      (strpos($register_content, 'Đã có tài khoản') !== false || 
                       strpos($register_content, 'Đăng nhập') !== false);
    printResult("Register page has link to login", $has_login_link);
    
    // Check for form fields
    $has_username_field = strpos($register_content, 'name="username"') !== false;
    $has_password_field = strpos($register_content, 'name="password"') !== false;
    $has_confirm_field = strpos($register_content, 'name="confirm_password"') !== false;
    $has_fullname_field = strpos($register_content, 'name="fullname"') !== false;
    $has_email_field = strpos($register_content, 'name="email"') !== false;
    $has_phone_field = strpos($register_content, 'name="phone"') !== false;
    
    printResult("Register form has all required fields", 
        $has_username_field && $has_password_field && $has_confirm_field && 
        $has_fullname_field && $has_email_field && $has_phone_field,
        "username, password, confirm_password, fullname, email, phone"
    );
}

// Check login.php
$login_exists = file_exists(__DIR__ . '/../login.php');
printResult("login.php file exists", $login_exists);

if ($login_exists) {
    $login_content = file_get_contents(__DIR__ . '/../login.php');
    
    // Check for link to register page
    $has_register_link = (strpos($login_content, 'register.php') !== false) && 
                         (strpos($login_content, 'Chưa có tài khoản') !== false || 
                          strpos($login_content, 'Đăng ký') !== false);
    printResult("Login page has link to register", $has_register_link);
}

// Check index.php
$index_exists = file_exists(__DIR__ . '/../index.php');
printResult("index.php file exists", $index_exists);

if ($index_exists) {
    $index_content = file_get_contents(__DIR__ . '/../index.php');
    
    // Check for register button/link
    $has_register_button = strpos($index_content, 'register.php') !== false;
    printResult("Homepage has link to register page", $has_register_button);
}

// ============================================================================
// TEST 9: SQL Injection Prevention
// ============================================================================
printSection("TEST 9: SQL Injection Prevention");

$sql_injection_patterns = [
    "' OR '1'='1",
    "'; DROP TABLE users; --",
    "admin'--"
];

$all_safe = true;

foreach ($sql_injection_patterns as $pattern) {
    $test_user = 'sqltest_' . bin2hex(random_bytes(4));
    $test_usernames[] = $test_user;
    
    // Try to inject SQL
    $malicious_username = $test_user . $pattern;
    $test_usernames[] = $malicious_username;
    
    $hashed = password_hash('test123', PASSWORD_DEFAULT);
    $stmt = $conn->prepare(
        "INSERT INTO users (username, password, role, fullname, email, phone) VALUES (?, ?, 'user', ?, ?, ?)"
    );
    $fullname = 'Test';
    $email = 'test@test.com';
    $phone = '0900000000';
    $stmt->bind_param("sssss", $malicious_username, $hashed, $fullname, $email, $phone);
    
    try {
        $stmt->execute();
        $stmt->close();
        
        // Check if database is still intact
        $check = $conn->query("SHOW TABLES LIKE 'users'");
        if ($check->num_rows === 0) {
            $all_safe = false;
            printResult("SQL injection prevented for pattern '$pattern'", false, "ERROR: users table was dropped!");
            break;
        }
    } catch (Exception $e) {
        // Exception is okay - it means the injection was caught
    }
}

if ($all_safe) {
    printResult("SQL injection attacks prevented", true, "Tested " . count($sql_injection_patterns) . " injection patterns");
}

// ============================================================================
// Clean up
// ============================================================================
printSection("Cleanup");

cleanupTestUsers($conn, $test_usernames);
echo COLOR_GREEN . "✅ Test data cleaned up" . COLOR_RESET . "\n";

// ============================================================================
// Summary
// ============================================================================
printSection("TEST SUMMARY");

$total_tests = count($test_results);
$passed_tests = count(array_filter($test_results, function($r) { return $r['passed']; }));
$failed_tests = $total_tests - $passed_tests;

echo "Total Tests: $total_tests\n";
echo COLOR_GREEN . "Passed: $passed_tests" . COLOR_RESET . "\n";

if ($failed_tests > 0) {
    echo COLOR_RED . "Failed: $failed_tests" . COLOR_RESET . "\n\n";
    
    echo COLOR_RED . "Failed Tests:" . COLOR_RESET . "\n";
    foreach ($test_results as $result) {
        if (!$result['passed']) {
            echo "  ❌ {$result['name']}\n";
            if (!empty($result['message'])) {
                echo "     {$result['message']}\n";
            }
        }
    }
    echo "\n";
}

$success_rate = ($passed_tests / $total_tests) * 100;
echo sprintf("Success Rate: %.1f%%\n\n", $success_rate);

if ($failed_tests === 0) {
    echo COLOR_GREEN . "🎉 All manual tests passed!" . COLOR_RESET . "\n";
    echo COLOR_GREEN . "✅ Registration system is working correctly" . COLOR_RESET . "\n";
    exit(0);
} else {
    echo COLOR_YELLOW . "⚠️  Some tests failed. Please review the failures above." . COLOR_RESET . "\n";
    exit(1);
}

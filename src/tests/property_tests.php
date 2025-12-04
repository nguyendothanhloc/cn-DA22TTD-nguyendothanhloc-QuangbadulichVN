<?php
/**
 * Property-Based Tests for User Registration
 * Feature: user-registration
 * 
 * These tests verify correctness properties across multiple random inputs
 * to ensure the registration system behaves correctly for all valid cases.
 * 
 * Run this file directly: php tests/property_tests.php
 */

// Include database connection
require_once __DIR__ . '/../db.php';

// Test configuration
define('TEST_ITERATIONS', 100);

// Track test results
$test_results = [];
$test_usernames = [];

/**
 * Generate random valid registration data
 */
function generateValidRegistrationData() {
    global $test_usernames;
    
    $random_suffix = bin2hex(random_bytes(4));
    $username = 'testuser_' . $random_suffix;
    $password = 'pass' . bin2hex(random_bytes(3)); // At least 6 chars
    
    $test_usernames[] = $username;
    
    return [
        'username' => $username,
        'password' => $password,
        'fullname' => 'Test User ' . $random_suffix,
        'email' => 'test_' . $random_suffix . '@example.com',
        'phone' => '09' . str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT)
    ];
}

/**
 * Simulate user registration
 */
function registerUser($conn, array $data) {
    $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare(
        "INSERT INTO users (username, password, role, fullname, email, phone) VALUES (?, ?, 'user', ?, ?, ?)"
    );
    $stmt->bind_param(
        "sssss",
        $data['username'],
        $hashed_password,
        $data['fullname'],
        $data['email'],
        $data['phone']
    );
    
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}

/**
 * Clean up test users
 */
function cleanupTestUsers($conn, $usernames) {
    if (empty($usernames)) {
        return;
    }
    
    $placeholders = implode(',', array_fill(0, count($usernames), '?'));
    $stmt = $conn->prepare("DELETE FROM users WHERE username IN ($placeholders)");
    
    $types = str_repeat('s', count($usernames));
    $stmt->bind_param($types, ...$usernames);
    $stmt->execute();
    $stmt->close();
}

/**
 * Feature: user-registration, Property 1: Valid registration creates user account
 * Validates: Requirements 1.2, 2.1
 * 
 * Property: For any valid registration data (unique username, matching passwords ≥6 chars, 
 * valid email/phone), submitting the registration form should create a new user account 
 * in the database with role 'user' and bcrypt-hashed password
 */
function testProperty1_ValidRegistrationCreatesUserAccount($conn) {
    global $test_usernames;
    
    echo "\n=== Property 1: Valid registration creates user account ===\n";
    echo "Validates: Requirements 1.2, 2.1\n";
    echo "Running " . TEST_ITERATIONS . " iterations...\n\n";
    
    $failures = [];
    
    for ($i = 0; $i < TEST_ITERATIONS; $i++) {
        // Generate random valid registration data
        $data = generateValidRegistrationData();
        
        // Register the user
        $registration_success = registerUser($conn, $data);
        
        if (!$registration_success) {
            $failures[] = "Iteration $i: Registration failed for username {$data['username']}";
            continue;
        }
        
        // Verify user was created in database
        $stmt = $conn->prepare(
            "SELECT id, username, password, role, fullname, email, phone FROM users WHERE username = ?"
        );
        $stmt->bind_param("s", $data['username']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $failures[] = "Iteration $i: User {$data['username']} not found in database after registration";
            $stmt->close();
            continue;
        }
        
        $user = $result->fetch_assoc();
        $stmt->close();
        
        // Verify role is 'user'
        if ($user['role'] !== 'user') {
            $failures[] = "Iteration $i: User {$data['username']} has role '{$user['role']}' instead of 'user'";
        }
        
        // Verify password is hashed with bcrypt (starts with $2y$)
        if (substr($user['password'], 0, 4) !== '$2y$') {
            $failures[] = "Iteration $i: Password for {$data['username']} is not bcrypt hashed";
        }
        
        // Verify password hash is correct
        if (!password_verify($data['password'], $user['password'])) {
            $failures[] = "Iteration $i: Password verification failed for {$data['username']}";
        }
        
        // Verify other fields match
        if ($user['fullname'] !== $data['fullname']) {
            $failures[] = "Iteration $i: Fullname mismatch for {$data['username']}";
        }
        
        if ($user['email'] !== $data['email']) {
            $failures[] = "Iteration $i: Email mismatch for {$data['username']}";
        }
        
        if ($user['phone'] !== $data['phone']) {
            $failures[] = "Iteration $i: Phone mismatch for {$data['username']}";
        }
        
        // Progress indicator
        if (($i + 1) % 10 === 0) {
            echo ".";
        }
    }
    
    echo "\n\n";
    
    if (empty($failures)) {
        echo "✅ PASSED: All " . TEST_ITERATIONS . " iterations successful\n";
        return true;
    } else {
        echo "❌ FAILED: " . count($failures) . " failures out of " . TEST_ITERATIONS . " iterations\n";
        foreach ($failures as $failure) {
            echo "  - $failure\n";
        }
        return false;
    }
}

// Run tests
echo "========================================\n";
echo "User Registration Property-Based Tests\n";
echo "========================================\n";

try {
    $result1 = testProperty1_ValidRegistrationCreatesUserAccount($conn);
    
    // Clean up
    echo "\nCleaning up test data...\n";
    cleanupTestUsers($conn, $test_usernames);
    echo "Cleanup complete.\n";
    
    // Summary
    echo "\n========================================\n";
    echo "Test Summary\n";
    echo "========================================\n";
    echo "Property 1: " . ($result1 ? "PASSED ✅" : "FAILED ❌") . "\n";
    
    if ($result1) {
        echo "\n🎉 All property tests passed!\n";
        exit(0);
    } else {
        echo "\n⚠️  Some property tests failed.\n";
        exit(1);
    }
    
} catch (Exception $e) {
    echo "\n❌ Error running tests: " . $e->getMessage() . "\n";
    
    // Try to clean up even on error
    if (!empty($test_usernames)) {
        cleanupTestUsers($conn, $test_usernames);
    }
    
    exit(1);
}

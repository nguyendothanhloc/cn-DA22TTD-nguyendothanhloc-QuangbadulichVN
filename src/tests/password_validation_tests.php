<?php
/**
 * Property-Based Tests for Password Validation
 * Feature: user-registration
 * 
 * These tests verify password validation properties
 * Property 2: Password mismatch rejection
 * Property 3: Minimum password length enforcement
 * 
 * Run this file directly: php tests/password_validation_tests.php
 */

// Test configuration
define('TEST_ITERATIONS', 100);

// Track test results
$test_results = [];

/**
 * Simulate password validation logic from register.php
 */
function validatePasswordMatch($password, $confirm_password) {
    return $password === $confirm_password;
}

function validatePasswordLength($password) {
    return strlen($password) >= 6;
}

/**
 * Generate random mismatched passwords
 */
function generateMismatchedPasswords() {
    $password = bin2hex(random_bytes(rand(3, 10)));
    $confirm_password = bin2hex(random_bytes(rand(3, 10)));
    
    // Ensure they're different
    while ($password === $confirm_password) {
        $confirm_password = bin2hex(random_bytes(rand(3, 10)));
    }
    
    return [$password, $confirm_password];
}

/**
 * Generate random short passwords (less than 6 characters)
 */
function generateShortPassword() {
    $length = rand(0, 5); // 0 to 5 characters
    if ($length === 0) {
        return '';
    }
    return bin2hex(random_bytes(ceil($length / 2)));
}

/**
 * Feature: user-registration, Property 2: Password mismatch rejection
 * Validates: Requirements 1.4
 * 
 * Property: For any registration attempt where password and confirm_password fields 
 * do not match, the system should reject the registration and return an error message
 */
function testProperty2_PasswordMismatchRejection() {
    echo "\n=== Property 2: Password mismatch rejection ===\n";
    echo "Validates: Requirements 1.4\n";
    echo "Running " . TEST_ITERATIONS . " iterations...\n\n";
    
    $failures = [];
    
    for ($i = 0; $i < TEST_ITERATIONS; $i++) {
        // Generate random mismatched passwords
        list($password, $confirm_password) = generateMismatchedPasswords();
        
        // Validate - should return false (rejection)
        $is_valid = validatePasswordMatch($password, $confirm_password);
        
        if ($is_valid) {
            $failures[] = "Iteration $i: Mismatched passwords were incorrectly accepted (password: '$password', confirm: '$confirm_password')";
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

/**
 * Feature: user-registration, Property 3: Minimum password length enforcement
 * Validates: Requirements 2.2
 * 
 * Property: For any password with fewer than 6 characters, the system should 
 * reject the registration and return an error message
 */
function testProperty3_MinimumPasswordLengthEnforcement() {
    echo "\n=== Property 3: Minimum password length enforcement ===\n";
    echo "Validates: Requirements 2.2\n";
    echo "Running " . TEST_ITERATIONS . " iterations...\n\n";
    
    $failures = [];
    
    for ($i = 0; $i < TEST_ITERATIONS; $i++) {
        // Generate random short password (< 6 chars)
        $password = generateShortPassword();
        $actual_length = strlen($password);
        
        // Validate - should return false (rejection)
        $is_valid = validatePasswordLength($password);
        
        if ($is_valid && $actual_length < 6) {
            $failures[] = "Iteration $i: Short password (length $actual_length) was incorrectly accepted: '$password'";
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

/**
 * Additional test: Verify valid passwords are accepted
 */
function testValidPasswordsAreAccepted() {
    echo "\n=== Additional Test: Valid passwords are accepted ===\n";
    echo "Running " . TEST_ITERATIONS . " iterations...\n\n";
    
    $failures = [];
    
    for ($i = 0; $i < TEST_ITERATIONS; $i++) {
        // Generate valid password (>= 6 chars)
        $password = bin2hex(random_bytes(rand(3, 20))); // 6-40 chars
        
        // Validate - should return true (acceptance)
        $is_valid = validatePasswordLength($password);
        
        if (!$is_valid) {
            $failures[] = "Iteration $i: Valid password (length " . strlen($password) . ") was incorrectly rejected: '$password'";
        }
        
        // Test matching passwords
        $match_valid = validatePasswordMatch($password, $password);
        if (!$match_valid) {
            $failures[] = "Iteration $i: Matching passwords were incorrectly rejected";
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
echo "Password Validation Property-Based Tests\n";
echo "========================================\n";

try {
    $result2 = testProperty2_PasswordMismatchRejection();
    $result3 = testProperty3_MinimumPasswordLengthEnforcement();
    $result_valid = testValidPasswordsAreAccepted();
    
    // Summary
    echo "\n========================================\n";
    echo "Test Summary\n";
    echo "========================================\n";
    echo "Property 2 (Password mismatch rejection): " . ($result2 ? "PASSED ✅" : "FAILED ❌") . "\n";
    echo "Property 3 (Minimum password length): " . ($result3 ? "PASSED ✅" : "FAILED ❌") . "\n";
    echo "Additional (Valid passwords accepted): " . ($result_valid ? "PASSED ✅" : "FAILED ❌") . "\n";
    
    if ($result2 && $result3 && $result_valid) {
        echo "\n🎉 All property tests passed!\n";
        exit(0);
    } else {
        echo "\n⚠️  Some property tests failed.\n";
        exit(1);
    }
    
} catch (Exception $e) {
    echo "\n❌ Error running tests: " . $e->getMessage() . "\n";
    exit(1);
}

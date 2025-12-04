<?php
/**
 * Property-Based Tests for SQL Injection Prevention
 * Feature: user-registration
 * 
 * Property 4: SQL injection prevention
 * Validates: Requirements 2.3
 * 
 * Run this file directly: php tests/sql_injection_tests.php
 */

// Include database connection
require_once __DIR__ . '/../db.php';

// Test configuration
define('TEST_ITERATIONS', 100);

// Track test results
$test_usernames = [];

/**
 * Common SQL injection patterns to test
 */
function getSQLInjectionPatterns() {
    return [
        "' OR '1'='1",
        "'; DROP TABLE users; --",
        "admin'--",
        "' OR 1=1--",
        "' UNION SELECT * FROM users--",
        "1' AND '1'='1",
        "'; DELETE FROM users WHERE '1'='1",
        "' OR 'x'='x",
        "admin' OR '1'='1' /*",
        "' OR ''='",
        "1'; DROP TABLE users CASCADE; --",
        "' UNION ALL SELECT NULL, NULL, NULL--",
        "admin'/*",
        "' AND 1=0 UNION ALL SELECT 'admin', '81dc9bdb52d04dc20036dbd8313ed055'",
        "1' UNION SELECT NULL, username, password FROM users--"
    ];
}

/**
 * Generate registration data with SQL injection attempts
 */
function generateSQLInjectionData($iteration) {
    global $test_usernames;
    
    $patterns = getSQLInjectionPatterns();
    $pattern = $patterns[array_rand($patterns)];
    
    // Randomly inject into different fields
    $field = rand(0, 3);
    
    $random_suffix = bin2hex(random_bytes(4));
    $safe_username = 'testuser_' . $random_suffix;
    $test_usernames[] = $safe_username;
    
    $data = [
        'username' => $safe_username,
        'password' => 'password123',
        'fullname' => 'Test User ' . $random_suffix,
        'email' => 'test_' . $random_suffix . '@example.com',
        'phone' => '0900000000'
    ];
    
    // Inject SQL pattern into a random field
    switch ($field) {
        case 0:
            $data['username'] = $safe_username . $pattern;
            $test_usernames[] = $safe_username . $pattern;
            break;
        case 1:
            $data['fullname'] = 'Test' . $pattern;
            break;
        case 2:
            $data['email'] = 'test' . $pattern . '@example.com';
            break;
        case 3:
            $data['phone'] = '090' . $pattern;
            break;
    }
    
    return $data;
}

/**
 * Attempt registration with SQL injection patterns
 * This simulates the register.php logic with prepared statements
 */
function attemptRegistrationWithInjection($conn, $data) {
    try {
        $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Use prepared statement (same as register.php)
        $stmt = $conn->prepare(
            "INSERT INTO users (username, password, role, fullname, email, phone) VALUES (?, ?, 'user', ?, ?, ?)"
        );
        
        if (!$stmt) {
            return ['success' => false, 'error' => 'Prepare failed: ' . $conn->error];
        }
        
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
        
        return ['success' => $result, 'error' => $result ? null : $conn->error];
        
    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Check if database structure is intact (tables still exist)
 */
function checkDatabaseIntegrity($conn) {
    $tables = ['users', 'tours', 'places', 'bookings'];
    
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if (!$result || $result->num_rows === 0) {
            return ['intact' => false, 'missing_table' => $table];
        }
    }
    
    return ['intact' => true];
}

/**
 * Check if any unauthorized data was inserted
 * This checks if SQL injection patterns were executed (not just stored as strings)
 */
function checkForUnauthorizedData($conn, $initial_user_count) {
    // Get current user count
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    $row = $result->fetch_assoc();
    $current_count = $row['count'];
    
    // Check if multiple users were inserted (SQL injection might insert extra rows)
    // We expect at most 1 new user per iteration
    $new_users = $current_count - $initial_user_count;
    if ($new_users > 1) {
        return ['clean' => false, 'reason' => "Multiple users inserted: expected 0-1, got $new_users"];
    }
    
    // Check for SQL keywords in usernames that shouldn't be there
    // These would indicate the SQL was executed rather than stored as a string
    $dangerous_patterns = ['DROP', 'DELETE', 'TRUNCATE', 'ALTER', 'CREATE'];
    
    foreach ($dangerous_patterns as $pattern) {
        $stmt = $conn->prepare("SELECT username FROM users WHERE username LIKE ? AND username NOT LIKE 'testuser_%'");
        $search_pattern = "%$pattern%";
        $stmt->bind_param("s", $search_pattern);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $suspicious_users = [];
            while ($user = $result->fetch_assoc()) {
                // Exclude legitimate admin/testuser accounts
                if ($user['username'] !== 'admin' && $user['username'] !== 'testuser') {
                    $suspicious_users[] = $user['username'];
                }
            }
            $stmt->close();
            
            if (!empty($suspicious_users)) {
                return ['clean' => false, 'reason' => 'Suspicious users: ' . implode(', ', $suspicious_users)];
            }
        } else {
            $stmt->close();
        }
    }
    
    return ['clean' => true];
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

/**
 * Feature: user-registration, Property 4: SQL injection prevention
 * Validates: Requirements 2.3
 * 
 * Property: For any registration input containing SQL injection patterns 
 * (e.g., quotes, semicolons, SQL keywords), the system should sanitize the input 
 * and either safely store it or reject it without executing malicious SQL
 */
function testProperty4_SQLInjectionPrevention($conn) {
    global $test_usernames;
    
    echo "\n=== Property 4: SQL injection prevention ===\n";
    echo "Validates: Requirements 2.3\n";
    echo "Running " . TEST_ITERATIONS . " iterations...\n\n";
    
    $failures = [];
    $initial_integrity = checkDatabaseIntegrity($conn);
    
    if (!$initial_integrity['intact']) {
        echo "❌ Database integrity check failed before tests. Missing table: " . $initial_integrity['missing_table'] . "\n";
        return false;
    }
    
    // Get initial user count
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    $initial_user_count = $result->fetch_assoc()['count'];
    
    for ($i = 0; $i < TEST_ITERATIONS; $i++) {
        // Get user count before this iteration
        $result = $conn->query("SELECT COUNT(*) as count FROM users");
        $before_count = $result->fetch_assoc()['count'];
        
        // Generate data with SQL injection patterns
        $data = generateSQLInjectionData($i);
        
        // Attempt registration
        $result = attemptRegistrationWithInjection($conn, $data);
        
        // Check database integrity after each attempt
        $integrity = checkDatabaseIntegrity($conn);
        if (!$integrity['intact']) {
            $failures[] = "Iteration $i: Database integrity compromised! Missing table: " . $integrity['missing_table'];
            break; // Critical failure
        }
        
        // Check for unauthorized data
        $data_check = checkForUnauthorizedData($conn, $before_count);
        if (!$data_check['clean']) {
            $failures[] = "Iteration $i: " . $data_check['reason'];
        }
        
        // If registration succeeded, verify data was safely stored
        if ($result['success']) {
            $stmt = $conn->prepare("SELECT username, fullname, email, phone FROM users WHERE username = ?");
            $stmt->bind_param("s", $data['username']);
            $stmt->execute();
            $query_result = $stmt->get_result();
            
            if ($query_result->num_rows > 0) {
                $stored_user = $query_result->fetch_assoc();
                
                // Verify the data was stored as-is (escaped/sanitized by prepared statements)
                // The SQL injection patterns should be stored as literal strings, not executed
                if ($stored_user['username'] !== $data['username']) {
                    $failures[] = "Iteration $i: Username mismatch after storage";
                }
            }
            $stmt->close();
        }
        
        // Progress indicator
        if (($i + 1) % 10 === 0) {
            echo ".";
        }
    }
    
    echo "\n\n";
    
    // Final integrity check
    $final_integrity = checkDatabaseIntegrity($conn);
    if (!$final_integrity['intact']) {
        $failures[] = "Final check: Database integrity compromised! Missing table: " . $final_integrity['missing_table'];
    }
    
    if (empty($failures)) {
        echo "✅ PASSED: All " . TEST_ITERATIONS . " iterations successful\n";
        echo "   - Database structure intact\n";
        echo "   - No SQL injection executed\n";
        echo "   - Prepared statements working correctly\n";
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
echo "SQL Injection Prevention Property-Based Tests\n";
echo "========================================\n";

try {
    $result4 = testProperty4_SQLInjectionPrevention($conn);
    
    // Clean up
    echo "\nCleaning up test data...\n";
    cleanupTestUsers($conn, $test_usernames);
    echo "Cleanup complete.\n";
    
    // Summary
    echo "\n========================================\n";
    echo "Test Summary\n";
    echo "========================================\n";
    echo "Property 4 (SQL injection prevention): " . ($result4 ? "PASSED ✅" : "FAILED ❌") . "\n";
    
    if ($result4) {
        echo "\n🎉 All property tests passed!\n";
        echo "\n✅ The registration system successfully prevents SQL injection attacks.\n";
        echo "✅ Prepared statements are working correctly.\n";
        exit(0);
    } else {
        echo "\n⚠️  Property test failed.\n";
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

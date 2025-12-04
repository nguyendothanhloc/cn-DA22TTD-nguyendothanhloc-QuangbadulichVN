<?php
/**
 * Run All Property-Based Tests
 * Feature: user-registration
 * 
 * This script runs all property-based tests for the user registration feature.
 * Run this file directly: php tests/run_all_tests.php
 */

echo "========================================\n";
echo "User Registration - All Property Tests\n";
echo "========================================\n\n";

$test_files = [
    'tests/property_tests.php' => 'Property 1: Valid registration creates user account',
    'tests/password_validation_tests.php' => 'Properties 2-3: Password validation',
    'tests/sql_injection_tests.php' => 'Property 4: SQL injection prevention'
];

$results = [];
$all_passed = true;

foreach ($test_files as $file => $description) {
    echo "Running: $description\n";
    echo str_repeat('-', 40) . "\n";
    
    // Run the test file by including it
    ob_start();
    try {
        include $file;
        $passed = true;
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        $passed = false;
    }
    $output = ob_get_clean();
    
    // Display output
    echo $output . "\n";
    
    // Determine if test passed based on output
    $passed = (strpos($output, '🎉 All property tests passed!') !== false || 
               strpos($output, 'PASSED ✅') !== false);
    
    $results[$description] = $passed;
    
    if (!$passed) {
        $all_passed = false;
    }
}

// Final summary
echo "\n========================================\n";
echo "Final Test Summary\n";
echo "========================================\n";

foreach ($results as $test => $passed) {
    $status = $passed ? "✅ PASSED" : "❌ FAILED";
    echo "$status - $test\n";
}

echo "\n";

if ($all_passed) {
    echo "🎉 All property-based tests passed!\n";
    echo "\n✅ The user registration system is working correctly:\n";
    echo "   - Valid registrations create user accounts with proper hashing\n";
    echo "   - Password validation works correctly\n";
    echo "   - SQL injection attacks are prevented\n";
    exit(0);
} else {
    echo "⚠️  Some tests failed. Please review the output above.\n";
    exit(1);
}

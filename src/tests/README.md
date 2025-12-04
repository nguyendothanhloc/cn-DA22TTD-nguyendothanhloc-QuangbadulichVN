# User Registration Property-Based Tests

This directory contains property-based tests for the user registration feature.

## Overview

Property-based testing verifies that correctness properties hold across many randomly generated inputs, providing stronger guarantees than example-based tests.

## Test Files

### 1. property_tests.php
**Property 1: Valid registration creates user account**
- Validates: Requirements 1.2, 2.1
- Tests that valid registration data creates a user account with proper role and bcrypt-hashed password
- Runs 100 iterations with random valid data

### 2. password_validation_tests.php
**Property 2: Password mismatch rejection**
- Validates: Requirements 1.4
- Tests that mismatched passwords are rejected

**Property 3: Minimum password length enforcement**
- Validates: Requirements 2.2
- Tests that passwords shorter than 6 characters are rejected
- Runs 100 iterations per property

### 3. sql_injection_tests.php
**Property 4: SQL injection prevention**
- Validates: Requirements 2.3
- Tests that SQL injection patterns are safely handled by prepared statements
- Verifies database integrity is maintained
- Runs 100 iterations with various SQL injection patterns

## Running Tests

### Run Individual Tests

```bash
# Property 1: Valid registration
php tests/property_tests.php

# Properties 2-3: Password validation
php tests/password_validation_tests.php

# Property 4: SQL injection prevention
php tests/sql_injection_tests.php
```

### Run All Tests

```bash
php tests/run_all_tests.php
```

## Requirements

- PHP 7.0 or higher
- MySQL database with `travel_booking` database
- Database connection configured in `db.php`

## Test Configuration

Each test runs 100 iterations by default. You can modify the `TEST_ITERATIONS` constant in each test file to change this.

## Cleanup

All tests automatically clean up test data after execution. Test users are created with the prefix `testuser_` and are deleted after tests complete.

## Exit Codes

- `0`: All tests passed
- `1`: Some tests failed

## Notes

- Tests use the same database connection as the application
- Prepared statements are tested to ensure SQL injection prevention
- Password hashing is verified to use bcrypt algorithm
- All test data is automatically cleaned up after execution

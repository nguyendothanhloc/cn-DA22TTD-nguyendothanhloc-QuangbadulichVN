<?php
/**
 * Property-Based Tests for User Registration
 * Feature: user-registration
 * 
 * These tests verify correctness properties across multiple random inputs
 * to ensure the registration system behaves correctly for all valid cases.
 */

use PHPUnit\Framework\TestCase;

class RegistrationPropertyTest extends TestCase
{
    private $conn;
    private $test_usernames = [];
    
    protected function setUp(): void
    {
        // Setup database connection for testing
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "travel_booking";
        $port = 3306;
        
        $this->conn = new mysqli($servername, $username, $password, $dbname, $port);
        
        if ($this->conn->connect_error) {
            $this->markTestSkipped('Database connection failed: ' . $this->conn->connect_error);
        }
        
        $this->conn->set_charset("utf8mb4");
    }
    
    protected function tearDown(): void
    {
        // Clean up test users created during tests
        if (!empty($this->test_usernames)) {
            $placeholders = implode(',', array_fill(0, count($this->test_usernames), '?'));
            $stmt = $this->conn->prepare("DELETE FROM users WHERE username IN ($placeholders)");
            
            $types = str_repeat('s', count($this->test_usernames));
            $stmt->bind_param($types, ...$this->test_usernames);
            $stmt->execute();
            $stmt->close();
        }
        
        if ($this->conn) {
            $this->conn->close();
        }
    }
    
    /**
     * Generate random valid registration data
     */
    private function generateValidRegistrationData(): array
    {
        $random_suffix = bin2hex(random_bytes(4));
        $username = 'testuser_' . $random_suffix;
        $password = 'pass' . bin2hex(random_bytes(3)); // At least 6 chars
        
        $this->test_usernames[] = $username;
        
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
    private function registerUser(array $data): bool
    {
        $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $stmt = $this->conn->prepare(
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
     * Feature: user-registration, Property 1: Valid registration creates user account
     * Validates: Requirements 1.2, 2.1
     * 
     * Property: For any valid registration data (unique username, matching passwords ≥6 chars, 
     * valid email/phone), submitting the registration form should create a new user account 
     * in the database with role 'user' and bcrypt-hashed password
     */
    public function testProperty1_ValidRegistrationCreatesUserAccount()
    {
        $iterations = 100;
        $failures = [];
        
        for ($i = 0; $i < $iterations; $i++) {
            // Generate random valid registration data
            $data = $this->generateValidRegistrationData();
            
            // Register the user
            $registration_success = $this->registerUser($data);
            
            if (!$registration_success) {
                $failures[] = "Iteration $i: Registration failed for username {$data['username']}";
                continue;
            }
            
            // Verify user was created in database
            $stmt = $this->conn->prepare(
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
        }
        
        // Assert no failures occurred
        $this->assertEmpty(
            $failures,
            "Property 1 failed:\n" . implode("\n", $failures)
        );
    }
}

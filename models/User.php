<?php
/**
 * User Model
 * Handles user authentication and data
 */

class User
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Authenticate user with credentials
     */
    public function authenticate($username, $password)
    {
        try {
            $stmt = $this->pdo->prepare('SELECT * FROM users WHERE username = ? OR email = ?');
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();

            if ($user && $password === $user['password']) {
                return $user;
            }
            return false;
        }
        catch (PDOException $e) {
            error_log('Auth error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user by ID
     */
    public function getUserById($id)
    {
        try {
            $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = ?');
            $stmt->execute([$id]);
            return $stmt->fetch();
        }
        catch (PDOException $e) {
            error_log('Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all users
     */
    public function getAllUsers()
    {
        try {
            $stmt = $this->pdo->query('SELECT id, username, email, full_name, created_at FROM users ORDER BY id DESC');
            return $stmt->fetchAll();
        }
        catch (PDOException $e) {
            error_log('Error getting users: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Create new user
     */
    public function createUser($username, $email, $password, $fullName)
    {
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO users (username, email, password, full_name) VALUES (?, ?, ?, ?)'
            );

            return $stmt->execute([$username, $email, $password, $fullName]);
        }
        catch (PDOException $e) {
            error_log('Error creating user: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update existing user
     */
    public function updateUser($id, $username, $email, $fullName, $password = null)
    {
        try {
            if ($password) {
                $stmt = $this->pdo->prepare(
                    'UPDATE users SET username = ?, email = ?, full_name = ?, password = ? WHERE id = ?'
                );
                return $stmt->execute([$username, $email, $fullName, $password, $id]);
            }
            else {
                $stmt = $this->pdo->prepare(
                    'UPDATE users SET username = ?, email = ?, full_name = ? WHERE id = ?'
                );
                return $stmt->execute([$username, $email, $fullName, $id]);
            }
        }
        catch (PDOException $e) {
            error_log('Error updating user: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete user
     */
    public function deleteUser($id)
    {
        try {
            $stmt = $this->pdo->prepare('DELETE FROM users WHERE id = ?');
            return $stmt->execute([$id]);
        }
        catch (PDOException $e) {
            error_log('Error deleting user: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if username or email already exists
     */
    public function checkUserExists($username, $email, $excludeId = null)
    {
        try {
            if ($excludeId) {
                $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM users WHERE (username = ? OR email = ?) AND id != ?');
                $stmt->execute([$username, $email, $excludeId]);
            }
            else {
                $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ? OR email = ?');
                $stmt->execute([$username, $email]);
            }
            return $stmt->fetchColumn() > 0;
        }
        catch (PDOException $e) {
            error_log('Error checking user existence: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Validate password strength
     */
    public function validatePassword($password)
    {
        if (strlen($password) < 6) {
            return false;
        }
        return true;
    }
}
?>

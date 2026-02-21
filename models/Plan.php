<?php
/**
 * Plan Model
 * Handles membership plans CRUD operations
 */

class Plan {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Get all plans
     */
    public function getAllPlans($onlyActive = true) {
        try {
            $query = 'SELECT * FROM membership_plans';
            if ($onlyActive) {
                $query .= ' WHERE is_active = 1';
            }
            $query .= ' ORDER BY duration ASC';
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get plan by ID
     */
    public function getPlanById($id) {
        try {
            $stmt = $this->pdo->prepare('SELECT * FROM membership_plans WHERE id = ?');
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log('Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create new plan
     */
    public function createPlan($name, $duration, $price, $description = '') {
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO membership_plans (name, duration, price, description) VALUES (?, ?, ?, ?)'
            );
            
            $result = $stmt->execute([$name, $duration, $price, $description]);
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log('Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update plan
     */
    public function updatePlan($id, $name, $duration, $price, $description = '', $isActive = true) {
        try {
            $stmt = $this->pdo->prepare(
                'UPDATE membership_plans SET name = ?, duration = ?, price = ?, description = ?, is_active = ? WHERE id = ?'
            );
            
            return $stmt->execute([$name, $duration, $price, $description, $isActive, $id]);
        } catch (PDOException $e) {
            error_log('Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete plan
     */
    public function deletePlan($id) {
        try {
            // Check if plan is used by members
            $stmt = $this->pdo->prepare('SELECT COUNT(*) as count FROM members WHERE plan_id = ?');
            $stmt->execute([$id]);
            $result = $stmt->fetch();
            
            if ($result['count'] > 0) {
                return ['success' => false, 'message' => 'Cannot delete plan with active members'];
            }
            
            $stmt = $this->pdo->prepare('DELETE FROM membership_plans WHERE id = ?');
            return ['success' => $stmt->execute([$id]), 'message' => 'Plan deleted successfully'];
        } catch (PDOException $e) {
            error_log('Error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error deleting plan'];
        }
    }
    
    /**
     * Validate plan data
     */
    public function validatePlan($name, $duration, $price) {
        $errors = [];
        
        if (empty($name) || strlen($name) < 2) {
            $errors[] = 'Plan name must be at least 2 characters';
        }
        
        if (!is_numeric($duration) || $duration <= 0) {
            $errors[] = 'Duration must be a positive number';
        }
        
        if (!is_numeric($price) || $price <= 0) {
            $errors[] = 'Price must be a positive number';
        }
        
        return $errors;
    }
}
?>

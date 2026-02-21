<?php
/**
 * FeeType Model
 * Manages fee types (Admission Fee, Cardio Fee, etc.)
 */

class FeeType {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Get all active fee types
     */
    public function getAllFeeTypes() {
        try {
            $stmt = $this->pdo->prepare(
                'SELECT * FROM fee_types WHERE is_active = 1 ORDER BY name ASC'
            );
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get fee type by ID
     */
    public function getFeeTypeById($id) {
        try {
            $stmt = $this->pdo->prepare('SELECT * FROM fee_types WHERE id = ?');
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log('Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Add new fee type
     */
    public function addFeeType($name, $defaultAmount = 0.00) {
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO fee_types (name, default_amount) VALUES (?, ?)'
            );
            $stmt->execute([$name, $defaultAmount]);
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log('Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update fee type
     */
    public function updateFeeType($id, $name, $defaultAmount) {
        try {
            $stmt = $this->pdo->prepare(
                'UPDATE fee_types SET name = ?, default_amount = ? WHERE id = ?'
            );
            return $stmt->execute([$name, $defaultAmount, $id]);
        } catch (PDOException $e) {
            error_log('Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete fee type (soft delete)
     */
    public function deleteFeeType($id) {
        try {
            $stmt = $this->pdo->prepare('UPDATE fee_types SET is_active = 0 WHERE id = ?');
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log('Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Validate fee type data
     */
    public function validateFeeType($name, $defaultAmount) {
        $errors = [];
        
        if (empty($name)) {
            $errors[] = 'Fee type name is required';
        }
        
        if (!is_numeric($defaultAmount) || $defaultAmount < 0) {
            $errors[] = 'Default amount must be a positive number';
        }
        
        return $errors;
    }
}
?>

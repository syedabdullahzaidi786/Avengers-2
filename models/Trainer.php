<?php
/**
 * Trainer Model
 * Handles trainer attributes, CRUD, and commissions
 */

class Trainer
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Get all trainers
     */
    public function getAllTrainers($search = '')
    {
        try {
            $query = 'SELECT * FROM trainers WHERE 1=1';
            $params = [];

            if (!empty($search)) {
                $query .= ' AND (name LIKE ? OR phone LIKE ?)';
                $params = ['%' . $search . '%', '%' . $search . '%'];
            }

            $query .= ' ORDER BY name ASC';

            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        }
        catch (PDOException $e) {
            error_log('Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get trainer by ID
     */
    public function getTrainerById($id)
    {
        try {
            $stmt = $this->pdo->prepare('SELECT * FROM trainers WHERE id = ?');
            $stmt->execute([$id]);
            return $stmt->fetch();
        }
        catch (PDOException $e) {
            error_log('Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create trainer
     */
    public function createTrainer($data)
    {
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO trainers (name, phone, specialization, commission_rate, fee) VALUES (?, ?, ?, ?, ?)'
            );

            // Default commission rate to 80 if not provided or empty
            $commissionRate = !empty($data['commission_rate']) ? $data['commission_rate'] : 80.00;
            $fee = isset($data['fee']) ? $data['fee'] : 0.00;

            $stmt->execute([
                $data['name'],
                $data['phone'],
                $data['specialization'] ?? null,
                $commissionRate,
                $fee
            ]);

            return $this->pdo->lastInsertId();
        }
        catch (PDOException $e) {
            error_log('Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update trainer
     */
    public function updateTrainer($id, $data)
    {
        try {
            $stmt = $this->pdo->prepare(
                'UPDATE trainers SET name = ?, phone = ?, specialization = ?, commission_rate = ?, fee = ? WHERE id = ?'
            );

            $stmt->execute([
                $data['name'],
                $data['phone'],
                $data['specialization'] ?? null,
                $data['commission_rate'] ?? 80.00,
                $data['fee'] ?? 0.00,
                $id
            ]);

            return true;
        }
        catch (PDOException $e) {
            error_log('Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete trainer
     */
    public function deleteTrainer($id)
    {
        try {
            $stmt = $this->pdo->prepare('DELETE FROM trainers WHERE id = ?');
            return $stmt->execute([$id]);
        }
        catch (PDOException $e) {
            error_log('Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get trainer commissions
     */
    public function getTrainerCommissions($trainerId, $startDate = null, $endDate = null)
    {
        try {
            $query = 'SELECT c.*, p.payment_date, m.full_name as member_name 
                      FROM commissions c 
                      JOIN payments p ON c.payment_id = p.id 
                      JOIN members m ON c.member_id = m.id 
                      WHERE c.trainer_id = ?';
            $params = [$trainerId];

            if ($startDate && $endDate) {
                $query .= ' AND p.payment_date BETWEEN ? AND ?';
                $params[] = $startDate;
                $params[] = $endDate;
            }

            $query .= ' ORDER BY p.payment_date DESC';

            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        }
        catch (PDOException $e) {
            error_log('Error: ' . $e->getMessage());
            return [];
        }
    }
    /**
     * Get yearly commissions summary for all trainers
     */
    public function getYearlyCommissionsSummary($year = null)
    {
        try {
            if (!$year)
                $year = date('Y');

            $query = 'SELECT t.id, t.name, SUM(c.amount) as total_commission, COUNT(c.id) as payment_count
                      FROM trainers t
                      LEFT JOIN commissions c ON t.id = c.trainer_id
                      LEFT JOIN payments p ON c.payment_id = p.id
                      WHERE YEAR(p.payment_date) = ? OR p.payment_date IS NULL
                      GROUP BY t.id, t.name
                      ORDER BY total_commission DESC';

            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$year]);
            return $stmt->fetchAll();
        }
        catch (PDOException $e) {
            error_log('Error getting yearly commissions summary: ' . $e->getMessage());
            return [];
        }
    }
    /**
     * Get monthly commissions summary for all trainers
     */
    public function getMonthlyCommissionsSummary($month, $year)
    {
        try {
            $query = 'SELECT t.id, t.name, SUM(c.amount) as total_commission, COUNT(c.id) as payment_count
                      FROM trainers t
                      LEFT JOIN commissions c ON t.id = c.trainer_id
                      LEFT JOIN payments p ON c.payment_id = p.id
                      WHERE MONTH(p.payment_date) = ? AND YEAR(p.payment_date) = ?
                      GROUP BY t.id, t.name
                      ORDER BY total_commission DESC';

            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$month, $year]);
            return $stmt->fetchAll();
        }
        catch (PDOException $e) {
            error_log('Error getting monthly commissions summary: ' . $e->getMessage());
            return [];
        }
    }
}
?>

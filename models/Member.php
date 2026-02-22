<?php
/**
 * Member Model
 * Handles member CRUD operations and status management
 */

class Member
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Get all members with pagination
     */
    public function getAllMembers($limit = 10, $offset = 0, $search = '')
    {
        try {
            $query = 'SELECT m.*, p.name as plan_name, p.duration, p.price, t.name as trainer_name 
                      FROM members m 
                      JOIN membership_plans p ON m.plan_id = p.id 
                      LEFT JOIN trainers t ON m.trainer_id = t.id 
                      WHERE 1=1';

            $params = [];
            if (!empty($search)) {
                $query .= ' AND (m.full_name LIKE ? OR m.phone LIKE ? OR m.id LIKE ?)';
                $params = ['%' . $search . '%', '%' . $search . '%', '%' . $search . '%'];
            }

            $query .= ' ORDER BY m.created_at DESC LIMIT ? OFFSET ?';
            $params[] = $limit;
            $params[] = $offset;

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
     * Get total member count with search
     */
    public function getTotalMembers($search = '')
    {
        try {
            $query = 'SELECT COUNT(*) as total FROM members WHERE 1=1';
            $params = [];

            if (!empty($search)) {
                $query .= ' AND (full_name LIKE ? OR phone LIKE ? OR id LIKE ?)';
                $params = ['%' . $search . '%', '%' . $search . '%', '%' . $search . '%'];
            }

            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            $result = $stmt->fetch();
            return $result['total'] ?? 0;
        }
        catch (PDOException $e) {
            error_log('Error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get member by ID
     */
    public function getMemberById($id)
    {
        try {
            $stmt = $this->pdo->prepare(
                'SELECT m.*, p.name as plan_name, p.duration, p.price, t.name as trainer_name, t.specialization 
                 FROM members m 
                 JOIN membership_plans p ON m.plan_id = p.id 
                 LEFT JOIN trainers t ON m.trainer_id = t.id 
                 WHERE m.id = ?'
            );
            $stmt->execute([$id]);
            return $stmt->fetch();
        }
        catch (PDOException $e) {
            error_log('Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create new member
     */
    public function createMember($data)
    {
        try {
            // Calculate end_date
            $plan = $this->getPlanById($data['plan_id']);
            $startDate = new DateTime($data['start_date']);
            $endDate = $startDate->modify('+' . $plan['duration'] . ' days');

            $stmt = $this->pdo->prepare(
                'INSERT INTO members (full_name, phone, gender, profile_picture, plan_id, trainer_id, start_date, end_date, status) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );

            $status = $endDate->format('Y-m-d') >= date('Y-m-d') ? 'active' : 'expired';

            $result = $stmt->execute([
                $data['full_name'],
                $data['phone'],
                $data['gender'] ?? 'Male',
                $data['profile_picture'] ?? null,
                $data['plan_id'],
                !empty($data['trainer_id']) ? $data['trainer_id'] : null,
                $data['start_date'],
                $endDate->format('Y-m-d'),
                $status
            ]);

            return $this->pdo->lastInsertId();
        }
        catch (PDOException $e) {
            error_log('Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update member
     */
    public function updateMember($id, $data)
    {
        try {
            // Get plan to recalculate end_date
            $plan = $this->getPlanById($data['plan_id']);
            $startDate = new DateTime($data['start_date']);
            $endDate = $startDate->modify('+' . $plan['duration'] . ' days');
            $status = $endDate->format('Y-m-d') >= date('Y-m-d') ? 'active' : 'expired';

            $stmt = $this->pdo->prepare(
                'UPDATE members SET full_name = ?, phone = ?, gender = ?, 
                 profile_picture = ?, plan_id = ?, trainer_id = ?, start_date = ?, end_date = ?, status = ? WHERE id = ?'
            );

            return $stmt->execute([
                $data['full_name'],
                $data['phone'],
                $data['gender'] ?? 'Male',
                $data['profile_picture'] ?? null,
                $data['plan_id'],
                !empty($data['trainer_id']) ? $data['trainer_id'] : null,
                $data['start_date'],
                $endDate->format('Y-m-d'),
                $status,
                $id
            ]);
        }
        catch (PDOException $e) {
            error_log('Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete member
     */
    public function deleteMember($id)
    {
        try {
            $stmt = $this->pdo->prepare('DELETE FROM members WHERE id = ?');
            return $stmt->execute([$id]);
        }
        catch (PDOException $e) {
            error_log('Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get expiring members (next 7 days)
     */
    public function getExpiringMembers()
    {
        try {
            $futureDate = date('Y-m-d', strtotime('+7 days'));
            $stmt = $this->pdo->prepare(
                'SELECT m.*, p.name as plan_name 
                 FROM members m 
                 JOIN membership_plans p ON m.plan_id = p.id 
                 WHERE m.status = "active" AND m.end_date <= ? AND m.end_date > CURDATE() 
                 ORDER BY m.end_date ASC'
            );
            $stmt->execute([$futureDate]);
            return $stmt->fetchAll();
        }
        catch (PDOException $e) {
            error_log('Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get expired members
     */
    public function getExpiredMembers()
    {
        try {
            $stmt = $this->pdo->prepare(
                'SELECT COUNT(*) as total FROM members WHERE status = "expired"'
            );
            $stmt->execute();
            $result = $stmt->fetch();
            return $result['total'] ?? 0;
        }
        catch (PDOException $e) {
            error_log('Error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get active members count
     */
    public function getActiveMembers()
    {
        try {
            $stmt = $this->pdo->prepare(
                'SELECT COUNT(*) as total FROM members WHERE status = "active"'
            );
            $stmt->execute();
            $result = $stmt->fetch();
            return $result['total'] ?? 0;
        }
        catch (PDOException $e) {
            error_log('Error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Update all members status to expired if their end_date is past current date
     * and they are currently marked as active.
     */
    public function updateAllExpiredMemberships()
    {
        try {
            // Count how many will be updated
            $countStmt = $this->pdo->prepare('SELECT COUNT(*) FROM members WHERE end_date < CURDATE() AND status = "active"');
            $countStmt->execute();
            $count = $countStmt->fetchColumn();

            if ($count > 0) {
                $updateStmt = $this->pdo->prepare('UPDATE members SET status = "expired" WHERE end_date < CURDATE() AND status = "active"');
                $updateStmt->execute();
            }
            
            return $count;
        }
        catch (PDOException $e) {
            error_log('Error in updateAllExpiredMemberships: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Validate member data
     */
    public function validateMember($data)
    {
        $errors = [];

        if (empty($data['full_name'])) {
            $errors[] = 'Full name is required';
        }

        if (empty($data['phone']) || !preg_match('/^[0-9\+\-\(\) ]+$/', $data['phone'])) {
            $errors[] = 'Valid phone number is required';
        }

        if (empty($data['plan_id'])) {
            $errors[] = 'Plan selection is required';
        }

        if (empty($data['start_date'])) {
            $errors[] = 'Start date is required';
        }

        return $errors;
    }

    /**
     * Get plan by ID (helper)
     */
    private function getPlanById($id)
    {
        try {
            $stmt = $this->pdo->prepare('SELECT * FROM membership_plans WHERE id = ?');
            $stmt->execute([$id]);
            return $stmt->fetch();
        }
        catch (PDOException $e) {
            return false;
        }
    }
}
?>

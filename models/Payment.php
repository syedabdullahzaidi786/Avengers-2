<?php
/**
 * Payment Model
 * Handles payment operations and reporting
 */

class Payment
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Get all payments with pagination
     */
    public function getAllPayments($limit = 10, $offset = 0)
    {
        try {
            $stmt = $this->pdo->prepare(
                'SELECT p.*, m.full_name, m.phone, ft.name as fee_type_name 
                 FROM payments p 
                 JOIN members m ON p.member_id = m.id 
                 LEFT JOIN fee_types ft ON p.fee_type_id = ft.id
                 ORDER BY p.payment_date DESC, p.created_at DESC 
                 LIMIT ? OFFSET ?'
            );
            $stmt->execute([$limit, $offset]);
            return $stmt->fetchAll();
        }
        catch (PDOException $e) {
            error_log('Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get payments by member ID
     */
    public function getPaymentsByMember($memberId)
    {
        try {
            $stmt = $this->pdo->prepare(
                'SELECT * FROM payments WHERE member_id = ? ORDER BY payment_date DESC'
            );
            $stmt->execute([$memberId]);
            return $stmt->fetchAll();
        }
        catch (PDOException $e) {
            error_log('Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get total payments count
     */
    public function getTotalPayments()
    {
        try {
            $stmt = $this->pdo->prepare('SELECT COUNT(*) as total FROM payments');
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
     * Add payment
     */
    public function addPayment($memberId, $amount, $paymentMethod, $paymentDate, $description = '', $feeTypeId = 1, $receiptNumber = null)
    {
        try {
            // Only start transaction if not already in one (to support bulk adds)
            $inTransaction = $this->pdo->inTransaction();
            if (!$inTransaction) {
                $this->pdo->beginTransaction();
            }

            if (!$receiptNumber) {
                $receiptNumber = 'REC-' . date('YmdHis') . '-' . $memberId;
            }

            $stmt = $this->pdo->prepare(
                'INSERT INTO payments (member_id, fee_type_id, amount, payment_method, payment_date, description, receipt_number) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            );

            $stmt->execute([
                $memberId,
                $feeTypeId,
                $amount,
                $paymentMethod,
                $paymentDate,
                $description,
                $receiptNumber
            ]);

            $paymentId = $this->pdo->lastInsertId();

            // Calculate Commission if member has a trainer
            $stmt = $this->pdo->prepare('SELECT trainer_id FROM members WHERE id = ?');
            $stmt->execute([$memberId]);
            $member = $stmt->fetch();

            if ($member && !empty($member['trainer_id'])) {
                // Check if this fee type is eligible for commission from database
                $stmt = $this->pdo->prepare('SELECT is_commissionable FROM fee_types WHERE id = ?');
                $stmt->execute([$feeTypeId]);
                $feeType = $stmt->fetch();
                $isCommissionable = $feeType ? (bool)$feeType['is_commissionable'] : false;

                if ($isCommissionable) {
                    // Get trainer commission rate
                    $stmt = $this->pdo->prepare('SELECT commission_rate FROM trainers WHERE id = ?');
                    $stmt->execute([$member['trainer_id']]);
                    $trainer = $stmt->fetch();

                    if ($trainer) {
                        $rate = $trainer['commission_rate']; // Percentage: e.g. 80
                        $commissionAmount = $amount * ($rate / 100);

                        // Insert commission record
                        $stmt = $this->pdo->prepare(
                            'INSERT INTO commissions (trainer_id, member_id, payment_id, amount, commission_rate) 
                             VALUES (?, ?, ?, ?, ?)'
                        );
                        $stmt->execute([
                            $member['trainer_id'],
                            $memberId,
                            $paymentId,
                            $commissionAmount,
                            $rate
                        ]);
                    }
                }
                else {
                    error_log("Payment for fee type ID: $feeTypeId is NOT commissionable.");
                }
            }

            if (!$inTransaction) {
                $this->pdo->commit();
            }
            return $paymentId;

        }
        catch (PDOException $e) {
            if (!$inTransaction) {
                $this->pdo->rollBack();
            }
            error_log('Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get recent payments (for dashboard)
     */
    public function getRecentPayments($limit = 5)
    {
        try {
            $stmt = $this->pdo->prepare(
                'SELECT p.*, m.full_name, m.phone 
                 FROM payments p 
                 JOIN members m ON p.member_id = m.id 
                 ORDER BY p.payment_date DESC, p.created_at DESC 
                 LIMIT ?'
            );
            $stmt->execute([$limit]);
            return $stmt->fetchAll();
        }
        catch (PDOException $e) {
            error_log('Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get monthly revenue
     */
    public function getMonthlyRevenue($month = null, $year = null)
    {
        try {
            if (!$month) {
                $month = date('m');
            }
            if (!$year) {
                $year = date('Y');
            }

            $stmt = $this->pdo->prepare(
                'SELECT SUM(amount) as total FROM payments 
                 WHERE MONTH(payment_date) = ? AND YEAR(payment_date) = ?'
            );
            $stmt->execute([$month, $year]);
            $result = $stmt->fetch();
            return $result['total'] ?? 0;
        }
        catch (PDOException $e) {
            error_log('Error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get total revenue
     */
    public function getTotalRevenue()
    {
        try {
            $stmt = $this->pdo->prepare('SELECT SUM(amount) as total FROM payments');
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
     * Get yearly revenue breakdown
     */
    public function getYearlyRevenueBreakdown($year = null)
    {
        try {
            if (!$year) {
                $year = date('Y');
            }

            $stmt = $this->pdo->prepare(
                'SELECT MONTH(payment_date) as month, SUM(amount) as total 
                 FROM payments 
                 WHERE YEAR(payment_date) = ? 
                 GROUP BY MONTH(payment_date) 
                 ORDER BY MONTH(payment_date) ASC'
            );
            $stmt->execute([$year]);
            return $stmt->fetchAll();
        }
        catch (PDOException $e) {
            error_log('Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Validate payment data
     */
    public function validatePayment($memberId, $amount, $paymentMethod)
    {
        $errors = [];

        if (empty($memberId)) {
            $errors[] = 'Member is required';
        }

        if (!is_numeric($amount) || $amount <= 0) {
            $errors[] = 'Amount must be a positive number';
        }

        $validMethods = ['cash', 'card', 'online', 'easypaisa', 'jazzcash', 'nayapay', 'sadapay', 'bank_transfer'];
        if (!in_array($paymentMethod, $validMethods)) {
            $errors[] = 'Invalid payment method: ' . $paymentMethod;
        }

        return $errors;
    }

    /**
     * Get payment by ID
     */
    public function getPaymentById($id)
    {
        try {
            $stmt = $this->pdo->prepare(
                'SELECT p.*, m.full_name, m.phone, ft.name as fee_type_name 
                 FROM payments p 
                 JOIN members m ON p.member_id = m.id 
                 LEFT JOIN fee_types ft ON p.fee_type_id = ft.id
                 WHERE p.id = ?'
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
     * Get payments by receipt number
     */
    public function getPaymentsByReceipt($receiptNumber)
    {
        try {
            $stmt = $this->pdo->prepare(
                'SELECT p.*, m.full_name, m.phone, ft.name as fee_type_name 
                 FROM payments p 
                 JOIN members m ON p.member_id = m.id 
                 LEFT JOIN fee_types ft ON p.fee_type_id = ft.id
                 WHERE p.receipt_number = ?'
            );
            $stmt->execute([$receiptNumber]);
            return $stmt->fetchAll();
        }
        catch (PDOException $e) {
            error_log('Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Delete payment
     */
    public function deletePayment($id)
    {
        try {
            $this->pdo->beginTransaction();

            // Delete associated commission first (cascade should handle this, but good to be explicit)
            $stmt = $this->pdo->prepare('DELETE FROM commissions WHERE payment_id = ?');
            $stmt->execute([$id]);

            // Delete payment
            $stmt = $this->pdo->prepare('DELETE FROM payments WHERE id = ?');
            $stmt->execute([$id]);

            $this->pdo->commit();
            return true;
        }
        catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log('Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update payment
     */
    public function updatePayment($id, $data)
    {
        try {
            $this->pdo->beginTransaction();

            // Update payment record
            $stmt = $this->pdo->prepare(
                'UPDATE payments SET member_id = ?, fee_type_id = ?, amount = ?, payment_method = ?, payment_date = ?, description = ? 
                 WHERE id = ?'
            );

            $result = $stmt->execute([
                $data['member_id'],
                $data['fee_type_id'] ?? 1,
                $data['amount'],
                $data['payment_method'],
                $data['payment_date'],
                $data['description'],
                $id
            ]);

            if (!$result) {
                throw new Exception("Failed to update payment");
            }

            // Update Commission if it exists
            // Check if there's an existing commission for this payment
            $stmt = $this->pdo->prepare('SELECT * FROM commissions WHERE payment_id = ?');
            $stmt->execute([$id]);
            $commission = $stmt->fetch();

            if ($commission) {
                // Recalculate commission based on new amount and stored rate
                $newCommissionAmount = $data['amount'] * ($commission['commission_rate'] / 100);

                $stmt = $this->pdo->prepare('UPDATE commissions SET amount = ? WHERE id = ?');
                $stmt->execute([$newCommissionAmount, $commission['id']]);
            }
            else {
            // If now assigned to a trainer but wasn't before? (Complex case, skipping for now unless requested)
            // Or simply re-run the addPayment logic? No, too risky.
            // Start simple: Update existing commission if present.
            }

            $this->pdo->commit();
            return true;

        }
        catch (Exception $e) {
            $this->pdo->rollBack();
            error_log('Error: ' . $e->getMessage());
            return false;
        }
    }
    /**
     * Get all payments for a specific month and year with full details
     */
    public function getMonthlyDetailedPayments($month, $year)
    {
        try {
            $stmt = $this->pdo->prepare(
                'SELECT p.*, m.full_name, m.phone, ft.name as fee_type_name 
                 FROM payments p 
                 JOIN members m ON p.member_id = m.id 
                 LEFT JOIN fee_types ft ON p.fee_type_id = ft.id
                 WHERE MONTH(p.payment_date) = ? AND YEAR(p.payment_date) = ?
                 ORDER BY p.payment_date DESC'
            );
            $stmt->execute([$month, $year]);
            return $stmt->fetchAll();
        }
        catch (PDOException $e) {
            error_log('Error getting monthly detailed payments: ' . $e->getMessage());
            return [];
        }
    }
}
?>

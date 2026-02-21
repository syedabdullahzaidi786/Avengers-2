<?php
/**
 * Expense Model
 * Handles expense management
 */
class Expense
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Get all expenses (with optional filters)
    public function getAllExpenses($search = '', $category = '', $month = '', $year = '')
    {
        try {
            $sql = "SELECT * FROM expenses WHERE 1=1";
            $params = [];

            if (!empty($search)) {
                $sql .= " AND (title LIKE ? OR description LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }

            if (!empty($category)) {
                $sql .= " AND category = ?";
                $params[] = $category;
            }

            if (!empty($month) && !empty($year)) {
                $sql .= " AND MONTH(expense_date) = ? AND YEAR(expense_date) = ?";
                $params[] = $month;
                $params[] = $year;
            }

            $sql .= " ORDER BY expense_date DESC, id DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        catch (PDOException $e) {
            error_log("Error getting expenses: " . $e->getMessage());
            return [];
        }
    }

    // Get single expense
    public function getExpenseById($id)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM expenses WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        catch (PDOException $e) {
            error_log("Error getting expense: " . $e->getMessage());
            return false;
        }
    }

    // Add new expense
    public function addExpense($data)
    {
        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO expenses (title, amount, expense_date, category, description) 
                 VALUES (?, ?, ?, ?, ?)"
            );

            $stmt->execute([
                $data['title'],
                $data['amount'],
                $data['expense_date'],
                $data['category'],
                $data['description']
            ]);

            return $this->pdo->lastInsertId();
        }
        catch (PDOException $e) {
            error_log("Error adding expense: " . $e->getMessage());
            return false;
        }
    }

    // Update expense
    public function updateExpense($id, $data)
    {
        try {
            $stmt = $this->pdo->prepare(
                "UPDATE expenses 
                 SET title = ?, amount = ?, expense_date = ?, category = ?, description = ? 
                 WHERE id = ?"
            );

            return $stmt->execute([
                $data['title'],
                $data['amount'],
                $data['expense_date'],
                $data['category'],
                $data['description'],
                $id
            ]);
        }
        catch (PDOException $e) {
            error_log("Error updating expense: " . $e->getMessage());
            return false;
        }
    }

    // Delete expense
    public function deleteExpense($id)
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM expenses WHERE id = ?");
            return $stmt->execute([$id]);
        }
        catch (PDOException $e) {
            error_log("Error deleting expense: " . $e->getMessage());
            return false;
        }
    }

    // Get total expenses for a specific period (for reports)
    public function getTotalExpenses($month = '', $year = '')
    {
        try {
            $sql = "SELECT SUM(amount) as total FROM expenses WHERE 1=1";
            $params = [];

            if (!empty($month) && !empty($year)) {
                $sql .= " AND MONTH(expense_date) = ? AND YEAR(expense_date) = ?";
                $params[] = $month;
                $params[] = $year;
            }
            elseif (!empty($year)) {
                $sql .= " AND YEAR(expense_date) = ?";
                $params[] = $year;
            }

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        }
        catch (PDOException $e) {
            error_log("Error calculating total expenses: " . $e->getMessage());
            return 0;
        }
    }

    // Get expense breakdown by category (for charts)
    public function getCategoryBreakdown($month = '', $year = '')
    {
        try {
            $sql = "SELECT category, SUM(amount) as total FROM expenses WHERE 1=1";
            $params = [];

            if (!empty($month) && !empty($year)) {
                $sql .= " AND MONTH(expense_date) = ? AND YEAR(expense_date) = ?";
                $params[] = $month;
                $params[] = $year;
            }

            $sql .= " GROUP BY category ORDER BY total DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        catch (PDOException $e) {
            error_log("Error getting expense breakdown: " . $e->getMessage());
            return [];
        }
    }

    // Get monthly expense breakdown for a specific year
    public function getMonthlyExpensesByYear($year = null)
    {
        try {
            if (!$year)
                $year = date('Y');

            $stmt = $this->pdo->prepare(
                'SELECT MONTH(expense_date) as month, SUM(amount) as total 
                 FROM expenses 
                 WHERE YEAR(expense_date) = ? 
                 GROUP BY MONTH(expense_date) 
                 ORDER BY MONTH(expense_date) ASC'
            );
            $stmt->execute([$year]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        catch (PDOException $e) {
            error_log("Error getting monthly expenses: " . $e->getMessage());
            return [];
        }
    }
}
?>

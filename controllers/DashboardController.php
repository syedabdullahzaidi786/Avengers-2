<?php
/**
 * Dashboard Controller
 * Handles dashboard data and display
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Member.php';
require_once __DIR__ . '/../models/Payment.php';
require_once __DIR__ . '/../models/Plan.php';

class DashboardController {
    private $memberModel;
    private $paymentModel;
    private $planModel;
    
    public function __construct($pdo) {
        $this->memberModel = new Member($pdo);
        $this->paymentModel = new Payment($pdo);
        $this->planModel = new Plan($pdo);
    }
    
    /**
     * Get dashboard data
     */
    public function getDashboardData() {
        return [
            'totalMembers' => $this->getTotalMembers(),
            'activeMembers' => $this->memberModel->getActiveMembers(),
            'expiredMembers' => $this->memberModel->getExpiredMembers(),
            'monthlyRevenue' => $this->paymentModel->getMonthlyRevenue(),
            'expiringMembers' => $this->memberModel->getExpiringMembers(),
            'recentPayments' => $this->paymentModel->getRecentPayments(),
            'monthlyBreakdown' => $this->paymentModel->getYearlyRevenueBreakdown()
        ];
    }
    
    /**
     * Get total members count
     */
    private function getTotalMembers() {
        return $this->memberModel->getTotalMembers();
    }
}
?>

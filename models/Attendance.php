<?php
/**
 * Attendance Model
 * Handles member attendance logging
 */

class Attendance
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Mark attendance (Check-in)
     * Returns array with success status and message
     */
    public function markAttendance($memberId)
    {
        try {
            // Check if member exists and is active
            $stmt = $this->pdo->prepare('SELECT status, full_name, end_date FROM members WHERE id = ?');
            $stmt->execute([$memberId]);
            $member = $stmt->fetch();

            if (!$member) {
                return ['success' => false, 'message' => 'Member not found'];
            }

            if ($member['status'] !== 'active') {
                return [
                    'success' => false,
                    'message' => 'Membership is ' . $member['status'] . '. Expired on: ' . $member['end_date'],
                    'member' => $member
                ];
            }

            // Check if already checked in today
            $today = date('Y-m-d');
            $stmt = $this->pdo->prepare(
                'SELECT id, check_in_time FROM attendance 
                 WHERE member_id = ? AND DATE(check_in_time) = ? 
                 ORDER BY check_in_time DESC LIMIT 1'
            );
            $stmt->execute([$memberId, $today]);
            $lastEntry = $stmt->fetch();

            if ($lastEntry) {
                // Prevent duplicate check-in if it's too soon (e.g. within 1 hour)
                $lastTime = strtotime($lastEntry['check_in_time']);
                $diffInMinutes = (time() - $lastTime) / 60;

                if ($diffInMinutes < 60) {
                    return [
                        'success' => false,
                        'message' => 'Attendance already marked recently at ' . date('h:i A', $lastTime) . '. Please wait before scanning again.'
                    ];
                }
            }

            // Insert check-in record
            $stmt = $this->pdo->prepare(
                'INSERT INTO attendance (member_id, check_in_time, status) VALUES (?, NOW(), "present")'
            );
            $stmt->execute([$memberId]);

            return [
                'success' => true,
                'message' => 'Welcome, ' . $member['full_name'] . '!',
                'member' => $member,
                'check_in_time' => date('H:i')
            ];

        }
        catch (PDOException $e) {
            error_log('Error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Database error'];
        }
    }

    /**
     * Get today's attendance
     */
    public function getTodayAttendance()
    {
        try {
            $today = date('Y-m-d');
            $stmt = $this->pdo->prepare(
                'SELECT a.*, m.full_name, m.phone 
                 FROM attendance a 
                 JOIN members m ON a.member_id = m.id 
                 WHERE DATE(a.check_in_time) = ? 
                 ORDER BY a.check_in_time DESC'
            );
            $stmt->execute([$today]);
            return $stmt->fetchAll();
        }
        catch (PDOException $e) {
            error_log('Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get member's attendance history
     */
    public function getMemberAttendance($memberId, $limit = 30)
    {
        try {
            $stmt = $this->pdo->prepare(
                'SELECT * FROM attendance WHERE member_id = ? ORDER BY check_in_time DESC LIMIT ?'
            );
            $stmt->execute([$memberId, $limit]);
            return $stmt->fetchAll();
        }
        catch (PDOException $e) {
            error_log('Error: ' . $e->getMessage());
            return [];
        }
    }
}
?>

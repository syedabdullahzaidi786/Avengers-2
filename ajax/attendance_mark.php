<?php
/**
 * AJAX - Mark Attendance
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Attendance.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }
    
    if (empty($_POST['member_id'])) {
        throw new Exception('Member ID is required');
    }
    
    $attendanceModel = new Attendance($pdo);
    $result = $attendanceModel->markAttendance($_POST['member_id']);
    
    echo json_encode($result);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

<?php
/**
 * AJAX - Get Daily Attendance
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Attendance.php';

header('Content-Type: application/json');

$attendanceModel = new Attendance($pdo);
$logs = $attendanceModel->getTodayAttendance();

echo json_encode($logs);
?>

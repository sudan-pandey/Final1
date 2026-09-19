<?php
/**
 * Real-time AJAX Endpoint: Membership & Leave Requests
 * Returns JSON list of pending join and leave requests for Club Head or Admin.
 */

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];
$role = $_SESSION['user_role'] ?? 'student';

try {
    $joinRequests = [];
    $leaveRequests = [];

    if ($role === 'admin') {
        // Admin views all pending join & leave requests across clubs
        $stmtJoin = $pdo->query("SELECT m.*, u.full_name AS student_name, u.email AS student_email, c.name AS club_name
                                 FROM memberships m
                                 JOIN users u ON m.user_id = u.id
                                 JOIN clubs c ON m.club_id = c.id
                                 WHERE m.status = 'pending'
                                 ORDER BY m.joined_at DESC");
        $joinRequests = $stmtJoin->fetchAll();

        $stmtLeave = $pdo->query("SELECT m.*, u.full_name AS student_name, u.email AS student_email, c.name AS club_name
                                  FROM memberships m
                                  JOIN users u ON m.user_id = u.id
                                  JOIN clubs c ON m.club_id = c.id
                                  WHERE m.status = 'active' AND m.leave_status = 'pending'
                                  ORDER BY m.joined_at DESC");
        $leaveRequests = $stmtLeave->fetchAll();

    } elseif ($role === 'club_head') {
        $club = getOwnClub($pdo, $userId);
        if ($club) {
            $clubId = $club['id'];

            $stmtJoin = $pdo->prepare("SELECT m.*, u.full_name AS student_name, u.email AS student_email
                                       FROM memberships m
                                       JOIN users u ON m.user_id = u.id
                                       WHERE m.club_id = ? AND m.status = 'pending'
                                       ORDER BY m.joined_at DESC");
            $stmtJoin->execute([$clubId]);
            $joinRequests = $stmtJoin->fetchAll();

            $stmtLeave = $pdo->prepare("SELECT m.*, u.full_name AS student_name, u.email AS student_email
                                        FROM memberships m
                                        JOIN users u ON m.user_id = u.id
                                        WHERE m.club_id = ? AND m.status = 'active' AND m.leave_status = 'pending'
                                        ORDER BY m.joined_at DESC");
            $stmtLeave->execute([$clubId]);
            $leaveRequests = $stmtLeave->fetchAll();
        }
    }

    echo json_encode([
        'success' => true,
        'join_count' => count($joinRequests),
        'leave_count' => count($leaveRequests),
        'total_pending' => count($joinRequests) + count($leaveRequests),
        'join_requests' => $joinRequests,
        'leave_requests' => $leaveRequests
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database exception: ' . $e->getMessage()]);
}

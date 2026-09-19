<?php
/**
 * Real-time AJAX Endpoint: Tasks Tracker
 * Returns JSON array of tasks and status progress for the current user.
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
    $tasks = [];

    if ($role === 'admin') {
        $stmt = $pdo->query("SELECT t.*, c.name AS club_name, u.full_name AS assignee_name
                             FROM tasks t
                             JOIN clubs c ON t.club_id = c.id
                             LEFT JOIN users u ON t.assigned_to = u.id
                             ORDER BY t.created_at DESC");
        $tasks = $stmt->fetchAll();

    } elseif ($role === 'club_head') {
        $club = getOwnClub($pdo, $userId);
        if ($club) {
            $clubId = $club['id'];
            $stmt = $pdo->prepare("SELECT t.*, c.name AS club_name, u.full_name AS assignee_name
                                   FROM tasks t
                                   JOIN clubs c ON t.club_id = c.id
                                   LEFT JOIN users u ON t.assigned_to = u.id
                                   WHERE t.club_id = ?
                                   ORDER BY t.created_at DESC");
            $stmt->execute([$clubId]);
            $tasks = $stmt->fetchAll();
        }

    } else {
        // Student
        $stmt = $pdo->prepare("SELECT t.*, c.name AS club_name
                               FROM tasks t
                               JOIN clubs c ON t.club_id = c.id
                               WHERE t.assigned_to = ?
                               ORDER BY t.deadline ASC");
        $stmt->execute([$userId]);
        $tasks = $stmt->fetchAll();
    }

    echo json_encode([
        'success' => true,
        'count' => count($tasks),
        'tasks' => $tasks
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database exception: ' . $e->getMessage()]);
}

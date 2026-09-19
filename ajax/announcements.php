<?php
/**
 * Real-time AJAX Endpoint: Announcements Feed
 * Returns JSON array of latest announcements accessible to the current user.
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
    if ($role === 'admin') {
        $stmt = $pdo->prepare("SELECT a.*, c.name AS club_name, u.full_name AS author_name,
                                 (SELECT read_at FROM announcement_reads ar WHERE ar.announcement_id = a.id AND ar.user_id = ?) AS read_at
                               FROM announcements a
                               LEFT JOIN clubs c ON a.club_id = c.id
                               JOIN users u ON a.created_by = u.id
                               WHERE a.scope != 'PRIVATE'
                               ORDER BY a.created_at DESC");
        $stmt->execute([$userId]);
    } elseif ($role === 'club_head') {
        $club = getOwnClub($pdo, $userId);
        $clubId = $club ? intval($club['id']) : 0;

        if ($clubId > 0) {
            $stmt = $pdo->prepare("SELECT a.*, c.name AS club_name, u.full_name AS author_name,
                                     (SELECT read_at FROM announcement_reads ar WHERE ar.announcement_id = a.id AND ar.user_id = ?) AS read_at
                                   FROM announcements a
                                   LEFT JOIN clubs c ON a.club_id = c.id
                                   JOIN users u ON a.created_by = u.id
                                   WHERE a.scope = 'GLOBAL' OR (a.scope IN ('CLUB', 'PRIVATE') AND a.club_id = ?)
                                   ORDER BY a.created_at DESC");
            $stmt->execute([$userId, $clubId]);
        } else {
            $stmt = $pdo->prepare("SELECT a.*, c.name AS club_name, u.full_name AS author_name,
                                     (SELECT read_at FROM announcement_reads ar WHERE ar.announcement_id = a.id AND ar.user_id = ?) AS read_at
                                   FROM announcements a
                                   LEFT JOIN clubs c ON a.club_id = c.id
                                   JOIN users u ON a.created_by = u.id
                                   WHERE a.scope = 'GLOBAL'
                                   ORDER BY a.created_at DESC");
            $stmt->execute([$userId]);
        }
    } else {
        // Student
        $membership = getActiveMembership($pdo, $userId);
        $clubId = $membership ? intval($membership['club_id']) : 0;

        if ($clubId > 0) {
            $stmt = $pdo->prepare("SELECT a.*, c.name AS club_name, u.full_name AS author_name,
                                     (SELECT read_at FROM announcement_reads ar WHERE ar.announcement_id = a.id AND ar.user_id = ?) AS read_at
                                   FROM announcements a
                                   LEFT JOIN clubs c ON a.club_id = c.id
                                   JOIN users u ON a.created_by = u.id
                                   WHERE a.scope = 'GLOBAL' OR (a.scope IN ('CLUB', 'PRIVATE') AND a.club_id = ?)
                                   ORDER BY a.created_at DESC");
            $stmt->execute([$userId, $clubId]);
        } else {
            $stmt = $pdo->prepare("SELECT a.*, c.name AS club_name, u.full_name AS author_name,
                                     (SELECT read_at FROM announcement_reads ar WHERE ar.announcement_id = a.id AND ar.user_id = ?) AS read_at
                                   FROM announcements a
                                   LEFT JOIN clubs c ON a.club_id = c.id
                                   JOIN users u ON a.created_by = u.id
                                   WHERE a.scope = 'GLOBAL'
                                   ORDER BY a.created_at DESC");
            $stmt->execute([$userId]);
        }
    }

    $announcements = $stmt->fetchAll();
    echo json_encode([
        'success' => true,
        'count' => count($announcements),
        'announcements' => $announcements
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database exception: ' . $e->getMessage()]);
}

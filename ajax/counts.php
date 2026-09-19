<?php
/**
 * Real-time AJAX Endpoint: System & Dashboard Stat Counts
 * Returns JSON containing updated counts and sidebar badge numbers based on user role.
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

$response = [
    'role' => $role,
    'timestamp' => time(),
    'counts' => [],
    'badges' => []
];

try {
    if ($role === 'admin') {
        // Admin Counts
        $totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $totalClubs = $pdo->query("SELECT COUNT(*) FROM clubs")->fetchColumn();
        $totalMembers = $pdo->query("SELECT COUNT(*) FROM memberships WHERE status='active'")->fetchColumn();
        $totalEvents = $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
        $totalTasks = $pdo->query("SELECT COUNT(*) FROM tasks")->fetchColumn();
        $totalFeedback = $pdo->query("SELECT COUNT(*) FROM feedback")->fetchColumn();

        $pendingRequests = $pdo->query("SELECT COUNT(*) FROM memberships WHERE status = 'pending' OR leave_status = 'pending'")->fetchColumn();

        $unreadAnnouncements = getUnreadAnnouncementsCount($pdo, $userId);
        $pendingTasksCount = getAllPendingTasksCount($pdo);

        $response['counts'] = [
            'total_users' => intval($totalUsers),
            'total_clubs' => intval($totalClubs),
            'total_members' => intval($totalMembers),
            'total_events' => intval($totalEvents),
            'total_tasks' => intval($totalTasks),
            'total_feedback' => intval($totalFeedback),
            'pending_requests' => intval($pendingRequests)
        ];

        $response['badges'] = [
            'announcements.php' => $unreadAnnouncements,
            'tasks.php' => $pendingTasksCount,
            'memberships.php' => intval($pendingRequests)
        ];

    } elseif ($role === 'club_head') {
        // Club Head Counts
        $club = getOwnClub($pdo, $userId);
        $totalMembers = 0;
        $upcomingEvents = 0;
        $pendingTasks = 0;
        $inProgressTasks = 0;
        $completedTasks = 0;
        $overdueTasks = 0;
        $pendingJoin = 0;
        $pendingLeave = 0;

        if ($club) {
            $clubId = $club['id'];

            $totalMembers = $pdo->prepare("SELECT COUNT(*) FROM memberships WHERE club_id = ? AND status = 'active'");
            $totalMembers->execute([$clubId]);
            $totalMembers = $totalMembers->fetchColumn();

            $upcomingEvents = $pdo->prepare("SELECT COUNT(*) FROM events WHERE club_id = ? AND status = 'upcoming'");
            $upcomingEvents->execute([$clubId]);
            $upcomingEvents = $upcomingEvents->fetchColumn();

            $stmtTasks = $pdo->prepare("SELECT status, deadline FROM tasks WHERE club_id = ?");
            $stmtTasks->execute([$clubId]);
            $tasksList = $stmtTasks->fetchAll();

            foreach ($tasksList as $t) {
                if ($t['status'] === 'pending') $pendingTasks++;
                elseif ($t['status'] === 'in_progress') $inProgressTasks++;
                elseif ($t['status'] === 'completed') $completedTasks++;

                if (isTaskOverdue($t['deadline'], $t['status'])) $overdueTasks++;
            }

            $stmtPendingJoin = $pdo->prepare("SELECT COUNT(*) FROM memberships WHERE club_id = ? AND status = 'pending'");
            $stmtPendingJoin->execute([$clubId]);
            $pendingJoin = $stmtPendingJoin->fetchColumn();

            $stmtPendingLeave = $pdo->prepare("SELECT COUNT(*) FROM memberships WHERE club_id = ? AND status = 'active' AND leave_status = 'pending'");
            $stmtPendingLeave->execute([$clubId]);
            $pendingLeave = $stmtPendingLeave->fetchColumn();
        }

        $unreadAnnouncements = getUnreadAnnouncementsCount($pdo, $userId);
        $pendingTasksCount = getClubHeadPendingTasksCount($pdo, $userId);
        $totalPendingRequests = intval($pendingJoin) + intval($pendingLeave);

        $response['counts'] = [
            'total_members' => intval($totalMembers),
            'upcoming_events' => intval($upcomingEvents),
            'pending_tasks' => intval($pendingTasks),
            'in_progress_tasks' => intval($inProgressTasks),
            'completed_tasks' => intval($completedTasks),
            'overdue_tasks' => intval($overdueTasks),
            'pending_join_requests' => intval($pendingJoin),
            'pending_leave_requests' => intval($pendingLeave)
        ];

        $response['badges'] = [
            'announcements.php' => $unreadAnnouncements,
            'tasks.php' => $pendingTasksCount,
            'members.php' => $totalPendingRequests
        ];

    } else {
        // Student / Club Member Counts
        $upcomingEventsCount = $pdo->query("SELECT COUNT(*) FROM events WHERE event_date >= NOW() AND status='upcoming'")->fetchColumn();

        $stmtPending = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE assigned_to = ? AND status = 'pending'");
        $stmtPending->execute([$userId]);
        $pendingTasksCount = $stmtPending->fetchColumn();

        $stmtProgress = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE assigned_to = ? AND status = 'in_progress'");
        $stmtProgress->execute([$userId]);
        $progressTasksCount = $stmtProgress->fetchColumn();

        $stmtCompleted = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE assigned_to = ? AND status = 'completed'");
        $stmtCompleted->execute([$userId]);
        $completedTasksCount = $stmtCompleted->fetchColumn();

        $unreadAnnouncements = getUnreadAnnouncementsCount($pdo, $userId);
        $myPendingTasks = getPendingTasksCount($pdo, $userId);

        $response['counts'] = [
            'pending_tasks' => intval($pendingTasksCount),
            'progress_tasks' => intval($progressTasksCount),
            'completed_tasks' => intval($completedTasksCount),
            'upcoming_events' => intval($upcomingEventsCount)
        ];

        $response['badges'] = [
            'announcements.php' => $unreadAnnouncements,
            'tasks.php' => $myPendingTasks
        ];
    }

    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database exception: ' . $e->getMessage()]);
}

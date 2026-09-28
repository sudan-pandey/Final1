<?php
require_once __DIR__ . '/config/database.php';

echo "Starting Database Seeding...\n";

// Disable Foreign Key checks for clean wiping and re-seeding
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");

$tables = [
    'announcement_reads',
    'task_comments',
    'tasks',
    'feedback',
    'announcements',
    'attendance',
    'registrations',
    'events',
    'memberships',
    'responsibilities',
    'clubs',
    'users',
    'system_settings'
];

foreach ($tables as $table) {
    $pdo->exec("TRUNCATE TABLE `$table`;");
}

$pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

echo "Tables truncated successfully.\n";

$passwordHashAdmin = password_hash('admin123', PASSWORD_DEFAULT);
$passwordHashStudent = password_hash('student123', PASSWORD_DEFAULT);

// 1. Users
$stmt = $pdo->prepare("INSERT INTO users (id, full_name, email, password, role, status) VALUES (?, ?, ?, ?, ?, ?)");
$users = [
    [1, 'College Admin', 'admin@admin.com', $passwordHashAdmin, 'admin', 'active'],
    [2, 'John Doe (Computer Club Head)', 'clubhead1@admin.com', $passwordHashAdmin, 'club_head', 'active'],
    [3, 'Sarah Smith (Sports Club Head)', 'clubhead2@admin.com', $passwordHashAdmin, 'club_head', 'active'],
    [4, 'Aarav Sharma', 'student1@student.com', $passwordHashStudent, 'student', 'active'],
    [5, 'Bina Adhikari', 'student2@student.com', $passwordHashStudent, 'student', 'active'],
    [6, 'Chirag Thapa', 'student3@student.com', $passwordHashStudent, 'student', 'active']
];
foreach ($users as $u) {
    $stmt->execute($u);
}
echo "Seeded users.\n";

// 2. Clubs
$stmt = $pdo->prepare("INSERT INTO clubs (id, name, description, club_head_id, email_subject, email_body) VALUES (?, ?, ?, ?, ?, ?)");
$clubs = [
    [1, 'Computer Club', 'Club for tech enthusiasts, organizing coding challenges, seminars, and web development workshops.', 2, 'Welcome to Computer Club!', 'Dear {student_name},\n\nCongratulations! Your join request for Computer Club has been approved.'],
    [2, 'Sports Club', 'Organizing intra-college athletic matches, football tournaments, and indoor games.', 3, 'Welcome to Sports Club!', 'Dear {student_name},\n\nWelcome to Sports Club! Get ready for exciting tournaments.'],
    [3, 'Cultural Club', 'Promoting art, drama, music, and literary contributions through exhibitions and talent shows.', NULL, NULL, NULL]
];
foreach ($clubs as $c) {
    $stmt->execute($c);
}
echo "Seeded clubs.\n";

// 3. Responsibilities
$stmt = $pdo->prepare("INSERT INTO responsibilities (id, name, description) VALUES (?, ?, ?)");
$responsibilities = [
    [1, 'Logistics Lead', 'Manages resource allocation, venue setup, and material supply.'],
    [2, 'Graphics Lead', 'Handles banner designs, visual elements, and graphics assets.'],
    [3, 'Technical Lead', 'Responsible for hardware setups, coding events, and technical assistance.'],
    [4, 'Social Media Lead', 'Manages online handles, outreach campaigns, and event promotion.'],
    [5, 'Finance Lead', 'Tracks budgets, sponsorships, and internal club expenditures.'],
    [6, 'Event Coordinator', 'Coordinates event flows, schedules, and handles guest management.']
];
foreach ($responsibilities as $r) {
    $stmt->execute($r);
}
echo "Seeded responsibilities.\n";

// 4. Memberships
$stmt = $pdo->prepare("INSERT INTO memberships (id, user_id, club_id, responsibility_id, status, leave_status, joined_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
$memberships = [
    [1, 4, 1, 2, 'active', 'none', '2025-01-10 10:00:00'],
    [2, 5, 1, 3, 'active', 'none', '2025-01-12 11:30:00'],
    [3, 6, 1, NULL, 'pending', 'none', NULL]
];
foreach ($memberships as $m) {
    $stmt->execute($m);
}
echo "Seeded memberships.\n";

// 5. Events
$stmt = $pdo->prepare("INSERT INTO events (id, club_id, title, description, event_date, location, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
$events = [
    [1, 1, 'Web Development Workshop 2025', 'Hands-on training session on PHP, HTML5, CSS3, and JavaScript web development.', '2025-11-15 10:00:00', 'IT Lab 2, Main Block', 'upcoming'],
    [2, 1, 'Annual Hackathon 2025', '24-hour inter-college coding competition with exciting project showcases and awards.', '2025-02-10 09:00:00', 'Main Auditorium', 'completed'],
    [3, 2, 'Inter-College Football Tournament', 'Annual sports event featuring teams across all BCA semesters.', '2025-12-01 08:00:00', 'College Sports Ground', 'upcoming']
];
foreach ($events as $e) {
    $stmt->execute($e);
}
echo "Seeded events.\n";

// 6. Registrations
$stmt = $pdo->prepare("INSERT INTO registrations (id, event_id, user_id, registered_at) VALUES (?, ?, ?, ?)");
$registrations = [
    [1, 1, 4, '2025-02-01 10:00:00'],
    [2, 1, 5, '2025-02-01 10:15:00'],
    [3, 2, 4, '2025-02-05 09:00:00'],
    [4, 2, 5, '2025-02-05 09:30:00']
];
foreach ($registrations as $reg) {
    $stmt->execute($reg);
}
echo "Seeded registrations.\n";

// 7. Attendance
$stmt = $pdo->prepare("INSERT INTO attendance (id, event_id, user_id, status) VALUES (?, ?, ?, ?)");
$attendance = [
    [1, 2, 4, 'present'],
    [2, 2, 5, 'present']
];
foreach ($attendance as $att) {
    $stmt->execute($att);
}
echo "Seeded attendance.\n";

// 8. Announcements
$stmt = $pdo->prepare("INSERT INTO announcements (id, club_id, scope, title, priority, content, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$announcements = [
    [1, NULL, 'GLOBAL', 'Welcome to BCA 4th Semester Club Portal', 'General', 'Welcome all students to the centralized college club management platform for event registrations and task coordination.', 1, '2025-01-01 09:00:00'],
    [2, 1, 'CLUB', 'Web Development Workshop Schedule Released', 'Urgent', 'The complete schedule and topic list for the upcoming Web Development Workshop is now available in the portal.', 2, '2025-02-01 14:00:00'],
    [3, 1, 'PRIVATE', 'Internal Executive Committee Meeting Notes', 'Announcement', 'Private note for active members: Please complete assigned tasks before the upcoming review session.', 2, '2025-02-05 16:00:00']
];
foreach ($announcements as $ann) {
    $stmt->execute($ann);
}
echo "Seeded announcements.\n";

// 9. Announcement Reads
$stmt = $pdo->prepare("INSERT INTO announcement_reads (announcement_id, user_id, read_at) VALUES (?, ?, ?)");
$annReads = [
    [1, 4, '2025-01-02 10:00:00'],
    [2, 4, '2025-02-02 11:00:00']
];
foreach ($annReads as $ar) {
    $stmt->execute($ar);
}
echo "Seeded announcement_reads.\n";

// 10. Tasks
$stmt = $pdo->prepare("INSERT INTO tasks (id, club_id, event_id, assigned_to, assigned_by, responsibility_id, title, description, priority, status, deadline, completed_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$tasks = [
    [1, 1, 1, 4, 2, 2, 'Design Workshop Promotional Banner', 'Create modern high-resolution banners and social media posters for Web Dev Workshop 2025.', 'High', 'in_progress', '2025-11-10', NULL],
    [2, 1, 2, 5, 2, 3, 'Setup Local Server & Database Repository', 'Configure server environments, PHP runtime, and MariaDB database schema for Hackathon.', 'Urgent', 'completed', '2025-02-08', '2025-02-07 16:30:00'],
    [3, 1, 1, 4, 2, 2, 'Prepare Workshop Certificate Templates', 'Draft completion certificate designs for all active participants and winners.', 'Medium', 'pending', '2025-11-12', NULL]
];
foreach ($tasks as $t) {
    $stmt->execute($t);
}
echo "Seeded tasks.\n";

// 11. Task Comments
$stmt = $pdo->prepare("INSERT INTO task_comments (id, task_id, user_id, comment, created_at) VALUES (?, ?, ?, ?, ?)");
$taskComments = [
    [1, 1, 4, 'Initial design concepts prepared in Figma. Requesting review from Club Head.', '2025-02-02 10:30:00'],
    [2, 1, 2, 'Looks excellent! Please ensure high contrast text for readability on dark background.', '2025-02-02 11:15:00'],
    [3, 2, 5, 'Database repository seeded and verified on local development environment.', '2025-02-07 16:00:00']
];
foreach ($taskComments as $tc) {
    $stmt->execute($tc);
}
echo "Seeded task_comments.\n";

// 12. Feedback
$stmt = $pdo->prepare("INSERT INTO feedback (id, event_id, user_id, rating, comments, submitted_at) VALUES (?, ?, ?, ?, ?, ?)");
$feedbacks = [
    [1, 2, 4, 5, 'Outstanding hackathon event! Excellent organization, great mentoring sessions, and amazing hospitality.', '2025-02-11 10:00:00'],
    [2, 2, 5, 4, 'Great learning opportunity! The coding challenges were well structured and challenging.', '2025-02-11 11:30:00']
];
foreach ($feedbacks as $f) {
    $stmt->execute($f);
}
echo "Seeded feedback.\n";

// 13. System Settings
$stmt = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
$systemSettings = [
    ['max_student_clubs', '5']
];
foreach ($systemSettings as $ss) {
    $stmt->execute($ss);
}
echo "Seeded system_settings.\n";

echo "Database Seeding Completed Successfully!\n";

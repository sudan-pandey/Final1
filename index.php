<?php
// Main landing redirect / welcome page
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If user is already logged in, redirect them to their respective dashboards
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['user_role'] ?? '';
    if ($role === 'admin') {
        header("Location: admin/dashboard.php");
        exit;
    } elseif ($role === 'club_head') {
        header("Location: club-head/dashboard.php");
        exit;
    } else {
        header("Location: student/dashboard.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>College Club Management System - Campus Community Hub</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="landing-body">
    <header class="landing-header">
        <div class="container header-container">
            <h1 class="logo">🎯 ClubManager</h1>
            <nav class="landing-nav">
                <a href="login.php" class="btn btn-outline">Login</a>
                <a href="register.php" class="btn btn-primary">Register</a>
            </nav>
        </div>
    </header>

    <main class="landing-main">
        <section class="hero-section">
            <div class="container hero-container">
                <div class="hero-content">
                    <span class="badge">✨ BCA College Campus Hub</span>
                    <h2>Your Campus Life, Perfectly Organized</h2>
                    <p>Discover student clubs, explore upcoming workshops, RSVP to events, manage tasks, and connect with your college community—all in one place.</p>
                    <div class="hero-actions">
                        <a href="register.php" class="btn btn-primary btn-lg">Join Campus Community</a>
                        <a href="login.php" class="btn btn-secondary btn-lg">Access Portal</a>
                    </div>
                </div>
            </div>
        </section>

        <section class="features-section">
            <div class="container">
                <h3 class="section-title">Built For Modern Student Life</h3>
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">🛡️</div>
                        <h4>Role & Joining Safeguards</h4>
                        <p>Strict "one active club per student" enforcement and designated responsibility tracking managed securely on the server side.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">📅</div>
                        <h4>Event Directory & RSVPs</h4>
                        <p>Explore campus workshops, competitions, and activities with automated calendar listings and check-ins.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">✅</div>
                        <h4>Task Coordination</h4>
                        <p>Club leaders delegate responsibilities directly to student members, tracking pending, in-progress, and completed items.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">📢</div>
                        <h4>Announcements & Feedback</h4>
                        <p>Stay informed with targeted announcements and share 1-5 star event feedback to improve future campus activities.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="landing-footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> College Club Management System. Built with Procedural PHP & MySQL.</p>
        </div>
    </footer>
</body>
</html>

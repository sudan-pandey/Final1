# College Club Management System - Memory & Developer Architecture Guide

This document serves as the primary system context, architectural reference, and memory log for human developers and AI assistants working on the **College Club Management System**.

---

## 1. Project Overview & Tech Stack

The **College Club Management System** is a lightweight, high-performance campus portal designed for managing student clubs, event registrations, attendance logs, task delegation, member responsibilities, announcements, and feedback reviews.

* **Backend Engine**: Pure Procedural PHP 8.x (Strictly avoiding OOP classes, REST frameworks, or modern composer dependencies).
* **Database Access**: PHP Data Objects (PDO) connected to MySQL/MariaDB (`college_club_management`) using prepared statements and error-safe wrappers.
* **Frontend Design System**:
  * Clean semantic HTML5.
  * Custom responsive CSS (`assets/css/style.css`) powered by CSS custom properties (variables), multi-layered shadow depth, native Light/Dark theme toggling (`data-theme="light"` / `data-theme="dark"`), and role-based accent color themes (`data-role="student"`, `data-role="club_head"`, `data-role="admin"`).
  * Vanilla JavaScript (`assets/js/script.js`) providing client-side interactions, theme persistence (`localStorage`), real-time AJAX polling, expandable announcement texts, star-rating interactions, modal overlays, and event hover popovers.

---

## 2. Directory & Core File Structure

```
├── admin/                     # Admin Role Pages
│   ├── announcements.php
│   ├── assign-head.php
│   ├── attendance.php
│   ├── calendar.php
│   ├── clubs.php
│   ├── create-club.php
│   ├── dashboard.php
│   ├── edit-club.php
│   ├── events.php
│   ├── feedback.php
│   ├── memberships.php
│   ├── profile.php
│   ├── registrations.php
│   ├── responsibilities.php
│   ├── tasks.php
│   └── users.php
├── ajax/                      # Realtime Dynamic Polling Endpoints (JSON)
│   ├── announcements.php
│   ├── counts.php
│   ├── requests.php
│   └── tasks.php
├── assets/
│   ├── css/
│   │   └── style.css          # Central Design System, Palette Grading & Media Queries
│   └── js/
│       └── script.js          # Client-side JavaScript interactions & polling
├── club-head/                 # Club Head Workspace Pages
│   ├── announcements.php
│   ├── attendance.php
│   ├── calendar.php
│   ├── club.php
│   ├── create-event.php
│   ├── create-task.php
│   ├── dashboard.php
│   ├── edit-event.php
│   ├── edit-task.php
│   ├── events.php
│   ├── feedback.php
│   ├── members.php
│   ├── profile.php
│   ├── registrations.php
│   ├── responsibilities.php
│   ├── task-details.php
│   └── tasks.php
├── config/
│   ├── database.php           # PDO Database Connection
│   └── mail.php.example       # SMTP configuration template
├── database/
│   ├── database.sql           # Schema definition & initial seed data
│   └── migration.sql          # Incremental schema migrations
├── includes/
│   ├── auth.php               # Role authorization & session checking
│   ├── csrf.php               # CSRF token generation & validation
│   ├── footer.php             # Reusable footer layout
│   ├── functions.php          # Helper wrappers & security escape functions
│   ├── header.php             # HTML head, HTTP security headers & body data-role tag
│   ├── mailer.php             # Standalone SMTP email helper
│   ├── navbar.php             # Top navigation bar & theme toggle
│   └── sidebar.php            # Dynamic role-based sidebar navigation
├── student/                   # Student Portal Pages
│   ├── announcements.php
│   ├── calendar.php
│   ├── clubs.php
│   ├── dashboard.php
│   ├── event.php
│   ├── events.php
│   ├── feedback.php
│   ├── join-club.php
│   ├── my-club.php
│   ├── profile.php
│   ├── register-event.php
│   ├── task.php
│   └── tasks.php
├── uploads/
│   └── clubs/                 # Uploaded club profile images / logos
├── changelog.md               # Version release history
├── MEMORY.md                  # Developer & AI system architecture guide
├── memory.md                  # Developer & AI system architecture guide
├── index.php                  # Public landing page
├── login.php                  # Dynamic user authentication
├── logout.php                 # Session destruction handler
└── register.php               # Student account registration
```

---

## 3. Database Schema Overview

The MySQL/MariaDB database (`college_club_management`) consists of 12 core tables:

1. `users`: Account credentials, names, emails, and roles (`student`, `club_head`, `admin`).
2. `clubs`: Club profiles, descriptions, logos, email approval template subjects (`email_subject`) and bodies (`email_body`), linked to `club_head_id`.
3. `responsibilities`: 10 pre-seeded leadership responsibility categories (e.g., Event Lead, Design Lead, Logistics Lead).
4. `memberships`: Student club membership records with statuses (`pending`, `active`, `inactive`, `rejected`), leave statuses (`none`, `pending`, `approved`, `rejected`), assigned `responsibility_id`, and timestamps.
5. `events`: Club events with status (`upcoming`, `completed`, `cancelled`), location, title, and date.
6. `registrations`: Event sign-ups linking `event_id` and `user_id`.
7. `attendance`: Check-in logs linking `event_id` and `user_id` with status (`present`, `absent`).
8. `announcements`: System and club broadcasts with scope (`GLOBAL`, `CLUB`, `PRIVATE`), priority (`General`, `Event`, `Urgent`, `Announcement`), and target club linkages.
9. `tasks`: Action items assigned by club heads to students with priority (`Low`, `Medium`, `High`, `Urgent`), deadline, status (`pending`, `in_progress`, `completed`, `cancelled`), and completion date.
10. `task_comments`: Progress comments and milestone discussion threads on tasks.
11. `feedback`: 1-5 star event ratings and written reviews from registered attendees.
12. `system_settings`: System configuration key-value storage (e.g. `max_student_clubs` controlling student active club joining limits).

---

## 4. Key Workflows & Business Logic

### Student Workflows
* **Club Joining Limit**: Controlled by the `max_student_clubs` setting in `system_settings` (1 to 5, default 5). Server checks existing active/pending memberships in `student/join-club.php` and presents a responsive modal popup if limit is reached.
* **Join Requests**: Submitted as `status = 'pending'` and pending approval by the respective Club Head.
* **Leave Requests**: Submitted from `student/my-club.php` as `leave_status = 'pending'`. The student remains an active member until approved by the Club Head.
* **Task Board & Progress**: Students view assigned tasks and update progress (`pending` -> `in_progress` -> `completed`). Strict IDOR ownership validation prevents unauthorized task access.
* **Event RSVPs & Feedback**: Students can register/unregister for upcoming events. After an event is marked `completed`, registered attendees can submit a 1-5 star rating and comments.

### Club Head Workflows
* **Profile & Custom Approval Email**: Club Heads can edit their club description, upload a logo (JPG/PNG/WEBP up to 5MB with safe unique file naming), and customize the membership approval email template with placeholders (`{student_name}`, `{club_name}`, `{club_head_name}`, `{student_email}`).
* **Membership Management**: Approving a join request updates membership to `active` and automatically triggers an approval notification email via `sendMembershipApprovalEmail()`. Approving a leave request marks membership `inactive` and cancels active pending tasks.
* **Task Delegation**: Club Heads create tasks assigned to active club members linked to responsibility categories or specific events. Cross-club delegation is strictly prevented.
* **Event Management & Manual Attendance**: Club Heads create/edit events and track attendee check-ins using radio button checklists on `club-head/attendance.php`.

### Admin Workflows
* **System Settings**: Admin manages the `max_student_clubs` policy setting in `admin/profile.php`.
* **User & Club Management**: Admin creates/edits clubs, assigns Club Heads, manages account roles/activation, and reviews system-wide auditing logs across all modules.

---

## 5. Security & UI Architecture Standards

* **Prepared Statements**: All database operations must strictly use PDO prepared statements with bound parameters (`$stmt->prepare()` & `$stmt->execute()`).
* **Input Escaping**: Output variables rendered in HTML must be wrapped in `escape()` (HTML entities escape wrapper in `includes/functions.php`).
* **CSRF Protection**: Form submissions must include `<?php csrfInput(); ?>` and be validated with `verifyCSRFToken()`.
* **HTTP Security Headers**: Set in `includes/header.php` before output (`X-Content-Type-Options: nosniff`, `X-Frame-Options: SAMEORIGIN`, `Referrer-Policy: strict-origin-when-cross-origin`).
* **Responsive Design**: All page layouts, card grids (`card-grid`, `club-card-item`), tables (`table-responsive`), forms, sidebars, and headers must degrade gracefully across mobile viewports (320px, 375px, 425px, 640px, 768px, 1024px+).
* **Native Dark Mode**: Toggled via button (`#themeToggleBtn`) and preserved in `localStorage`. Uses CSS variables under `html[data-theme="dark"]` and `@media (prefers-color-scheme: dark)`.

---

## 6. Changelog History Summary

* **v1.0.0**: Initial repository setup, PDO database wrapper, Admin user, seed clubs and responsibilities.
* **v1.1.0 - v1.3.0**: Core auth helpers, CSRF protection, dynamic layouts, Admin CRUD, Club Head assigner, auditing grids.
* **v1.4.0 - v1.6.0**: Student hub, event directory, RSVP system, task board, comments thread, feedback rating system, Club Head workspace, attendance tracker.
* **v1.7.0 - v1.9.0**: Accessibility focus-visible outlines, ARIA labels, HTTP response security headers.
* **v2.0.0**: Pending join/leave request workflows, configurable club joining limit, custom approval email templates, logo uploads, multi-viewport responsive media queries, and real-time polling.
* **v2.1.0 (Current)**: Refined Campus Portal UI color grading (modern slate tones, indigo-violet gradients, role accents), enhanced mobile responsiveness across all pages, stacked club card layouts, responsive sidebar navigation, and full memory documentation (`MEMORY.md` & `memory.md`).

---

*Note for AI Agents & Developers*: When adding features or fixing bugs in this codebase, maintain procedural PHP conventions, preserve security checks, ensure dark mode compatibility, and verify layout responsiveness on mobile screens (down to 320px).

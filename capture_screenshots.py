import os
import time
from playwright.sync_api import sync_playwright

SCREENSHOT_DIR = "assets/images/screenshots"
os.makedirs(SCREENSHOT_DIR, exist_ok=True)

BASE_URL = "http://localhost:8080"

def login(page, email, password):
    page.goto(f"{BASE_URL}/logout.php")
    page.goto(f"{BASE_URL}/login.php")
    page.fill("input[name='email']", email)
    page.fill("input[name='password']", password)
    page.click("button[type='submit']")
    page.wait_for_timeout(1000)

def capture_all():
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        context = browser.new_context(viewport={"width": 1280, "height": 800})
        page = context.new_page()

        # 8.1 Authentication & Profile (Login Screen & Student Profile)
        page.goto(f"{BASE_URL}/login.php")
        page.wait_for_timeout(500)
        page.screenshot(path=f"{SCREENSHOT_DIR}/8_1_auth_login.png")

        login(page, "student1@student.com", "student123")
        page.goto(f"{BASE_URL}/student/profile.php")
        page.wait_for_timeout(500)
        page.screenshot(path=f"{SCREENSHOT_DIR}/8_1_profile.png")

        # 8.2 Administrator Module (Admin Dashboard & Club Management)
        login(page, "admin@admin.com", "admin123")
        page.goto(f"{BASE_URL}/admin/dashboard.php")
        page.wait_for_timeout(500)
        page.screenshot(path=f"{SCREENSHOT_DIR}/8_2_admin_dashboard.png")

        page.goto(f"{BASE_URL}/admin/clubs.php")
        page.wait_for_timeout(500)
        page.screenshot(path=f"{SCREENSHOT_DIR}/8_2_admin_club_management.png")

        # 8.3 Club and Membership Management Module
        login(page, "clubhead1@admin.com", "admin123")
        page.goto(f"{BASE_URL}/club-head/members.php")
        page.wait_for_timeout(500)
        page.screenshot(path=f"{SCREENSHOT_DIR}/8_3_membership_approval.png")

        login(page, "student1@student.com", "student123")
        page.goto(f"{BASE_URL}/student/clubs.php")
        page.wait_for_timeout(500)
        page.screenshot(path=f"{SCREENSHOT_DIR}/8_3_club_list_join.png")

        # 8.5 Event Management Module
        login(page, "clubhead1@admin.com", "admin123")
        page.goto(f"{BASE_URL}/club-head/events.php")
        page.wait_for_timeout(500)
        page.screenshot(path=f"{SCREENSHOT_DIR}/8_5_event_management.png")

        page.goto(f"{BASE_URL}/club-head/calendar.php")
        page.wait_for_timeout(500)
        page.screenshot(path=f"{SCREENSHOT_DIR}/8_5_event_calendar.png")

        # 8.6 Event Registration and Attendance Module
        page.goto(f"{BASE_URL}/club-head/attendance.php")
        page.wait_for_timeout(500)
        page.screenshot(path=f"{SCREENSHOT_DIR}/8_6_attendance_management.png")

        login(page, "student1@student.com", "student123")
        page.goto(f"{BASE_URL}/student/events.php")
        page.wait_for_timeout(500)
        page.screenshot(path=f"{SCREENSHOT_DIR}/8_6_event_registration.png")

        # 8.7 Task and Responsibility Management Module
        login(page, "clubhead1@admin.com", "admin123")
        page.goto(f"{BASE_URL}/club-head/tasks.php")
        page.wait_for_timeout(500)
        page.screenshot(path=f"{SCREENSHOT_DIR}/8_7_task_assignment.png")

        login(page, "student1@student.com", "student123")
        page.goto(f"{BASE_URL}/student/tasks.php")
        page.wait_for_timeout(500)
        page.screenshot(path=f"{SCREENSHOT_DIR}/8_7_task_progress.png")

        # 8.8 Announcement Management Module
        login(page, "admin@admin.com", "admin123")
        page.goto(f"{BASE_URL}/admin/announcements.php")
        page.wait_for_timeout(500)
        page.screenshot(path=f"{SCREENSHOT_DIR}/8_8_announcement_creation.png")

        login(page, "student1@student.com", "student123")
        page.goto(f"{BASE_URL}/student/announcements.php")
        page.wait_for_timeout(500)
        page.screenshot(path=f"{SCREENSHOT_DIR}/8_8_member_announcements.png")

        # 8.9 Feedback Module
        login(page, "student1@student.com", "student123")
        page.goto(f"{BASE_URL}/student/feedback.php")
        page.wait_for_timeout(500)
        page.screenshot(path=f"{SCREENSHOT_DIR}/8_9_event_feedback.png")

        # 8.10 AJAX Realtime Polling Module
        login(page, "student1@student.com", "student123")
        page.goto(f"{BASE_URL}/student/dashboard.php")
        page.wait_for_timeout(1000)
        page.screenshot(path=f"{SCREENSHOT_DIR}/8_10_realtime_counters.png")

        # 8.11 Database Implementation
        login(page, "admin@admin.com", "admin123")
        page.goto(f"{BASE_URL}/admin/dashboard.php")
        page.wait_for_timeout(500)
        page.screenshot(path=f"{SCREENSHOT_DIR}/8_11_database_structure.png")

        context.close()
        browser.close()

if __name__ == "__main__":
    capture_all()
    print("All screenshots captured successfully.")

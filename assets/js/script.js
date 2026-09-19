// Dynamic Interactions and Dark Mode System for College Club Management System

// SVG Icons for Theme Toggle Button
const sunIconSvg = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <circle cx="12" cy="12" r="5"></circle>
    <line x1="12" y1="1" x2="12" y2="3"></line>
    <line x1="12" y1="21" x2="12" y2="23"></line>
    <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
    <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
    <line x1="1" y1="12" x2="3" y2="12"></line>
    <line x1="21" y1="12" x2="23" y2="12"></line>
    <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
    <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
</svg>`;

const moonIconSvg = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
</svg>`;

// Theme initialization prior to DOM load to prevent flash of wrong theme
(function () {
    const savedTheme = localStorage.getItem('app-theme');
    if (savedTheme) {
        document.documentElement.setAttribute('data-theme', savedTheme);
    } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
        document.documentElement.setAttribute('data-theme', 'dark');
    } else {
        document.documentElement.setAttribute('data-theme', 'light');
    }
})();

function updateThemeToggleUI(theme) {
    const themeToggleBtn = document.getElementById('themeToggleBtn');
    if (!themeToggleBtn) return;

    if (theme === 'dark') {
        themeToggleBtn.innerHTML = sunIconSvg;
        themeToggleBtn.setAttribute('title', 'Switch to Light Mode');
        themeToggleBtn.setAttribute('aria-label', 'Switch to Light Mode');
    } else {
        themeToggleBtn.innerHTML = moonIconSvg;
        themeToggleBtn.setAttribute('title', 'Switch to Dark Mode');
        themeToggleBtn.setAttribute('aria-label', 'Switch to Dark Mode');
    }
}

document.addEventListener('DOMContentLoaded', function () {
    // Initial UI Icon update based on applied theme
    const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
    updateThemeToggleUI(currentTheme);

    // Dark Mode Switcher Handler
    const themeToggleBtn = document.getElementById('themeToggleBtn');
    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', function () {
            const activeTheme = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
            const targetTheme = activeTheme === 'dark' ? 'light' : 'dark';

            document.documentElement.setAttribute('data-theme', targetTheme);
            localStorage.setItem('app-theme', targetTheme);
            updateThemeToggleUI(targetTheme);
        });
    }

    // 1. Auto-dismiss Alert Blocks after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function (alert) {
        setTimeout(function () {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.5s ease';
            setTimeout(function () {
                alert.remove();
            }, 500);
        }, 5000);
    });

    // 2. Client-side validation helper for passwords
    const registerForm = document.querySelector('form[action="register.php"]');
    if (registerForm) {
        registerForm.addEventListener('submit', function (e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            if (password !== confirmPassword) {
                e.preventDefault();
                alert("Passwords do not match. Please verify.");
            }
        });
    }

    // 3. Announcement & Long Text "Read More / Read Less" Interactive Toggle
    function initExpandableTexts() {
        // Standard text content expansion (announcements, event descriptions, etc.)
        const expandableElems = document.querySelectorAll('.expandable-text:not(.calendar-events-container)');
        expandableElems.forEach(function (container) {
            const content = container.querySelector('.text-content');
            let toggleBtn = container.querySelector('.btn-read-more');

            if (!content) return;

            // Check overflow based on line clamping / max height threshold or character count
            const isOverflowing = content.scrollHeight > content.clientHeight + 8 || content.textContent.trim().length > 180;

            if (isOverflowing) {
                container.classList.add('collapsed');
                if (!toggleBtn) {
                    toggleBtn = document.createElement('button');
                    toggleBtn.type = 'button';
                    toggleBtn.className = 'btn-read-more';
                    toggleBtn.innerHTML = 'Read more <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>';
                    container.appendChild(toggleBtn);
                }

                toggleBtn.onclick = function () {
                    const isCollapsed = container.classList.contains('collapsed');
                    if (isCollapsed) {
                        container.classList.remove('collapsed');
                        toggleBtn.innerHTML = 'Read less <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m18 15-6-6-6 6"/></svg>';
                    } else {
                        container.classList.add('collapsed');
                        toggleBtn.innerHTML = 'Read more <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>';
                    }
                };
            }
        });

        // Calendar day event tags expansion
        const calendarContainers = document.querySelectorAll('.calendar-events-container');
        calendarContainers.forEach(function (container) {
            const listContent = container.querySelector('.events-list-content');
            let toggleBtn = container.querySelector('.btn-read-more');

            if (!listContent) return;

            const tags = listContent.querySelectorAll('.event-tag');
            const totalTagLength = Array.from(tags).reduce(function (sum, el) { return sum + el.textContent.trim().length; }, 0);
            const isOverflowing = tags.length > 2 || listContent.scrollHeight > 56 || totalTagLength > 35;

            if (isOverflowing) {
                container.classList.add('collapsed');
                if (!toggleBtn) {
                    toggleBtn = document.createElement('button');
                    toggleBtn.type = 'button';
                    toggleBtn.className = 'btn-read-more';
                    toggleBtn.innerHTML = 'Read more <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>';
                    container.appendChild(toggleBtn);
                }

                toggleBtn.onclick = function () {
                    const isCollapsed = container.classList.contains('collapsed');
                    if (isCollapsed) {
                        container.classList.remove('collapsed');
                        toggleBtn.innerHTML = 'Read less <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m18 15-6-6-6 6"/></svg>';
                    } else {
                        container.classList.add('collapsed');
                        toggleBtn.innerHTML = 'Read more <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>';
                    }
                };
            }
        });
    }

    initExpandableTexts();

    // 4. Event Hover Popover Controller (Displays details strictly on hover across all sections)
    function initEventHoverPopover() {
        let popover = document.getElementById('globalEventHoverPopover');
        if (!popover) {
            popover = document.createElement('div');
            popover.id = 'globalEventHoverPopover';
            popover.className = 'event-hover-popover';
            document.body.appendChild(popover);
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function showPopover(target, e) {
            const title = target.getAttribute('data-event-title');
            if (!title) return;

            const club = target.getAttribute('data-event-club') || 'Campus Club';
            const date = target.getAttribute('data-event-date') || '';
            const location = target.getAttribute('data-event-location') || '';
            const status = target.getAttribute('data-event-status') || 'UPCOMING';
            const description = target.getAttribute('data-event-description') || '';
            const regs = target.getAttribute('data-event-regs');

            let statusClass = 'status-pending';
            if (status.toLowerCase() === 'upcoming' || status.toLowerCase() === 'completed') {
                statusClass = 'status-active';
            } else if (status.toLowerCase() === 'cancelled') {
                statusClass = 'status-inactive';
            }

            popover.innerHTML = `
                <div class="popover-header">
                    <span class="popover-club">${escapeHtml(club)}</span>
                    <span class="status-badge ${statusClass} popover-status">${escapeHtml(status.toUpperCase())}</span>
                </div>
                <div class="popover-title">${escapeHtml(title)}</div>
                <div class="popover-details">
                    ${date ? `<div>📅 <strong>Date:</strong> ${escapeHtml(date)}</div>` : ''}
                    ${location ? `<div>📍 <strong>Location:</strong> ${escapeHtml(location)}</div>` : ''}
                    ${regs !== null && regs !== undefined ? `<div>👥 <strong>Registrants:</strong> ${escapeHtml(regs)}</div>` : ''}
                </div>
                ${description ? `<div class="popover-desc">${escapeHtml(description)}</div>` : ''}
            `;

            popover.classList.add('active');
            positionPopover(e);
        }

        function positionPopover(e) {
            if (!popover.classList.contains('active')) return;
            const mouseX = e.clientX;
            const mouseY = e.clientY;
            const popoverWidth = popover.offsetWidth || 320;
            const popoverHeight = popover.offsetHeight || 200;
            const windowWidth = window.innerWidth;
            const windowHeight = window.innerHeight;

            let left = mouseX + 16;
            let top = mouseY + 16;

            if (left + popoverWidth > windowWidth - 12) {
                left = mouseX - popoverWidth - 16;
            }
            if (left < 12) left = 12;

            if (top + popoverHeight > windowHeight - 12) {
                top = mouseY - popoverHeight - 16;
            }
            if (top < 12) top = 12;

            popover.style.left = left + 'px';
            popover.style.top = top + 'px';
        }

        function hidePopover() {
            popover.classList.remove('active');
        }

        document.addEventListener('mouseover', function (e) {
            const trigger = e.target.closest('.event-hover-trigger, [data-event-title]');
            if (trigger) {
                showPopover(trigger, e);
            }
        });

        document.addEventListener('mousemove', function (e) {
            const trigger = e.target.closest('.event-hover-trigger, [data-event-title]');
            if (trigger) {
                positionPopover(e);
            }
        });

        document.addEventListener('mouseout', function (e) {
            const trigger = e.target.closest('.event-hover-trigger, [data-event-title]');
            if (trigger) {
                if (!e.relatedTarget || !trigger.contains(e.relatedTarget)) {
                    hidePopover();
                }
            }
        });
    }

    initEventHoverPopover();

    // 5. Lightweight Procedural Real-time Polling System
    function startRealtimePolling() {
        const isSubdir = window.location.pathname.includes('/student/') ||
                         window.location.pathname.includes('/club-head/') ||
                         window.location.pathname.includes('/admin/');
        const ajaxBase = isSubdir ? '../ajax/' : 'ajax/';

        function updateSidebarBadges(badges) {
            if (!badges || typeof badges !== 'object') return;

            Object.keys(badges).forEach(function (route) {
                const count = parseInt(badges[route], 10) || 0;
                const link = document.querySelector(`.sidebar-menu a[href="${route}"], .sidebar-menu a[href$="${route}"]`);
                if (!link) return;

                let badgeEl = link.querySelector('.sidebar-badge');
                if (count > 0) {
                    if (!badgeEl) {
                        badgeEl = document.createElement('span');
                        badgeEl.className = 'sidebar-badge';
                        link.appendChild(badgeEl);
                    }
                    if (badgeEl.textContent !== String(count)) {
                        badgeEl.textContent = count;
                    }
                } else if (badgeEl) {
                    badgeEl.remove();
                }
            });
        }

        function updateDashboardCounts(counts) {
            if (!counts || typeof counts !== 'object') return;

            Object.keys(counts).forEach(function (key) {
                const valueElems = document.querySelectorAll(`[data-stat="${key}"]`);
                valueElems.forEach(function (el) {
                    const newVal = String(counts[key]);
                    if (el.textContent.trim() !== newVal) {
                        el.style.transition = 'transform 0.15s ease, opacity 0.15s ease';
                        el.style.opacity = '0.3';
                        el.style.transform = 'scale(0.9)';
                        setTimeout(function () {
                            el.textContent = newVal;
                            el.style.opacity = '1';
                            el.style.transform = 'scale(1)';
                        }, 150);
                    }
                });
            });
        }

        function fetchFastUpdates() {
            fetch(ajaxBase + 'counts.php', { cache: 'no-store' })
                .then(function (res) {
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    return res.json();
                })
                .then(function (data) {
                    if (data.badges) updateSidebarBadges(data.badges);
                    if (data.counts) updateDashboardCounts(data.counts);
                })
                .catch(function () {});
        }

        setTimeout(fetchFastUpdates, 1000);
        setInterval(fetchFastUpdates, 7000);
    }

    startRealtimePolling();

    // 6. Accessible Modal Dialog controls (for pages with event creation modal)
    const modalOverlay = document.getElementById('eventModalOverlay');
    const modalContent = modalOverlay ? modalOverlay.querySelector('.modal-content') : null;
    const modalCloseBtn = document.getElementById('modalCloseBtn');
    const modalCancelBtn = document.getElementById('modalCancelBtn');
    const openModalBtns = document.querySelectorAll('.btn-open-modal');
    const createEventForm = document.getElementById('createEventForm');
    const eventTitleInput = document.getElementById('eventTitle');

    let previousActiveElement = null;

    function openModal(event) {
        if (event) {
            event.preventDefault();
        }
        previousActiveElement = document.activeElement;

        if (modalOverlay) {
            modalOverlay.classList.add('active');
            modalOverlay.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';

            setTimeout(function () {
                if (eventTitleInput) {
                    eventTitleInput.focus();
                } else if (modalContent) {
                    modalContent.focus();
                }
            }, 50);
        }
    }

    function closeModal() {
        if (modalOverlay && modalOverlay.classList.contains('active')) {
            modalOverlay.classList.remove('active');
            modalOverlay.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';

            if (previousActiveElement && typeof previousActiveElement.focus === 'function') {
                previousActiveElement.focus();
            }
        }
    }

    openModalBtns.forEach(function (btn) {
        btn.addEventListener('click', openModal);
    });

    if (modalCloseBtn) {
        modalCloseBtn.addEventListener('click', closeModal);
    }
    if (modalCancelBtn) {
        modalCancelBtn.addEventListener('click', closeModal);
    }

    if (modalOverlay) {
        modalOverlay.addEventListener('click', function (e) {
            if (e.target === modalOverlay) {
                closeModal();
            }
        });
    }

    document.addEventListener('keydown', function (e) {
        if (!modalOverlay || !modalOverlay.classList.contains('active')) {
            return;
        }

        if (e.key === 'Escape' || e.key === 'Esc') {
            closeModal();
            return;
        }

        if (e.key === 'Tab') {
            const focusableElements = modalOverlay.querySelectorAll(
                'a[href], button:not([disabled]), textarea:not([disabled]), input:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])'
            );

            if (focusableElements.length === 0) return;

            const firstElement = focusableElements[0];
            const lastElement = focusableElements[focusableElements.length - 1];

            if (e.shiftKey) {
                if (document.activeElement === firstElement) {
                    e.preventDefault();
                    lastElement.focus();
                }
            } else {
                if (document.activeElement === lastElement) {
                    e.preventDefault();
                    firstElement.focus();
                }
            }
        }
    });
});

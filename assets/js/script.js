// Simple Dynamic Interactions for College Club Management System

document.addEventListener('DOMContentLoaded', function () {
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

    // 3. Accessible Modal Dialog controls (for pages with event creation modal)
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

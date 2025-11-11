<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Star Recruiting')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/favicon.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-purple: #3F1E6F;
            --accent-pink: #F03A6F;
            --dark-purple: #2D1447;
            --light-purple: #5A3A8A;
            --bg-gradient: linear-gradient(135deg, #3F1E6F 0%, #2D1447 100%);
            --card-shadow: 0 8px 32px rgba(63, 30, 111, 0.15);
            --hover-shadow: 0 12px 48px rgba(240, 58, 111, 0.25);
        }

        * {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%);
            min-height: 100vh;
            color: #2d3748;
        }

        .navbar {
            background: var(--bg-gradient) !important;
            box-shadow: 0 4px 20px rgba(63, 30, 111, 0.2);
            padding: 1rem 0;
        }

        /* Dashboard Layout */
        .dashboard-container {
            display: flex;
            min-height: 100vh;
            background: #1A004D;
            color: white;
            overflow: hidden;
        }

        .dashboard-sidebar {
            width: 16rem;
            background: #23005C;
            display: flex;
            flex-direction: column;
            padding: 1.5rem;
            border-right: 1px solid rgba(255, 255, 255, 0.1);
        }

        .dashboard-sidebar-brand {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 2rem;
        }

        .dashboard-sidebar-brand img {
            height: 24px;
            width: auto;
        }

        .dashboard-sidebar-brand h1 {
            font-size: 1.25rem;
            font-weight: 900;
            color: #FF3B6B;
            margin: 0;
        }

        .dashboard-sidebar-nav {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .dashboard-sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            color: #d1d5db;
            text-decoration: none;
            border-radius: 0.75rem;
            transition: all 0.3s ease;
            font-size: 0.875rem;
        }

        .dashboard-sidebar-nav a:hover,
        .dashboard-sidebar-nav a.active {
            background: #2A2A5A;
            color: white;
        }

        .dashboard-sidebar-nav-icon {
            width: 18px;
            height: 18px;
        }

        .dashboard-main {
            flex: 1;
            padding: 2rem;
            overflow-y: auto;
        }

        .dashboard-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }

        .dashboard-header h2 {
            font-size: 1.5rem;
            font-weight: 900;
            margin: 0;
        }


        .dashboard-search {
            background: #2A2A5A;
            color: white;
            border: none;
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            width: 16rem;
            font-size: 0.875rem;
        }

        .dashboard-search::placeholder {
            color: #9ca3af;
        }

        .dashboard-search:focus {
            outline: none;
            box-shadow: 0 0 0 2px rgba(255, 59, 107, 0.3);
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #fff !important;
            font-weight: 700;
            font-size: 1.5rem;
            text-decoration: none;
            transition: transform 0.2s ease;
        }

        .navbar-brand:hover {
            transform: translateY(-2px);
            color: var(--accent-pink) !important;
        }

        .navbar-brand img {
            height: 40px;
            width: auto;
        }

        .navbar-nav .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
            padding: 0.5rem 1rem !important;
            margin: 0 0.25rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .navbar-nav .nav-link:hover {
            background: rgba(240, 58, 111, 0.2);
            color: #fff !important;
            transform: translateY(-1px);
        }

        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(63, 30, 111, 0.15);
            margin-bottom: 24px;
            background: #fff;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 12px 48px rgba(240, 58, 111, 0.25);
            transform: translateY(-2px);
        }

        .card-header {
            background: var(--bg-gradient);
            color: #fff;
            border: none;
            padding: 1.25rem 1.5rem;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .card-body {
            padding: 1.5rem;
        }

        .btn-primary {
            background: var(--bg-gradient);
            border: none;
            border-radius: 10px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(240, 58, 111, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(240, 58, 111, 0.4);
            background: linear-gradient(135deg, #F03A6F 0%, #3F1E6F 100%);
        }

        .btn-secondary {
            background: #6c757d;
            border: none;
            border-radius: 10px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        .btn-success {
            background: #10b981;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-success:hover {
            background: #059669;
            transform: translateY(-2px);
        }

        .btn-danger {
            background: #ef4444;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-danger:hover {
            background: #dc2626;
            transform: translateY(-2px);
        }

        .btn-outline-primary {
            border: 2px solid var(--accent-pink);
            color: var(--accent-pink);
            border-radius: 10px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            background: transparent;
            transition: all 0.3s ease;
        }

        .btn-outline-primary:hover {
            background: var(--accent-pink);
            color: #fff;
            transform: translateY(-2px);
        }

        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e2e8f0;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--accent-pink);
            box-shadow: 0 0 0 3px rgba(240, 58, 111, 0.1);
        }

        .badge {
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
            font-weight: 600;
        }

        .progress {
            border-radius: 10px;
            height: 10px;
            background: #e2e8f0;
        }

        .progress-bar {
            background: var(--bg-gradient);
            border-radius: 10px;
        }

        h1, h2, h3, h4, h5, h6 {
            font-weight: 700;
            color: var(--primary-purple);
        }

        .text-muted {
            color: #718096 !important;
        }

        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.75rem;
            line-height: 1;
        }

        .btn-lg {
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            line-height: 1.5;
        }

        .alert {
            border-radius: 12px;
            border: none;
            padding: 1rem 1.5rem;
        }

        .table {
            border-radius: 12px;
            overflow: hidden;
        }

        .table thead {
            background: var(--bg-gradient);
            color: #fff;
        }

        .table tbody tr {
            transition: all 0.2s ease;
        }

        .table tbody tr:hover {
            background: #f7fafc;
            transform: scale(1.01);
        }

        body.app-modal-open {
            overflow: hidden;
        }

        .app-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(10, 7, 25, 0.68);
            backdrop-filter: blur(3px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            padding: 1.5rem;
        }

        .app-modal-overlay[data-visible="true"] {
            display: flex;
        }

        .app-modal {
            width: 100%;
            max-width: 520px;
            background: #1F0C4D;
            color: #fff;
            border-radius: 18px;
            box-shadow: 0 30px 80px rgba(6, 0, 40, 0.55);
            overflow: hidden;
            transform: translateY(18px);
            opacity: 0;
            transition: all 0.22s ease;
        }

        .app-modal.show {
            transform: translateY(0);
            opacity: 1;
        }

        .app-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.5rem;
            background: var(--bg-gradient);
        }

        .app-modal-header.success { background: linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%); }
        .app-modal-header.danger { background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%); }
        .app-modal-header.warning { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
        .app-modal-header.info { background: var(--bg-gradient); }

        .app-modal-close {
            border: none;
            background: transparent;
            color: rgba(255, 255, 255, 0.85);
            font-size: 1.75rem;
            line-height: 1;
            cursor: pointer;
            transition: transform 0.2s ease, color 0.2s ease;
        }

        .app-modal-close:hover {
            color: #fff;
            transform: scale(1.05);
        }

        .app-modal-body {
            padding: 1.5rem;
            background: rgba(46, 26, 95, 0.65);
            font-size: 0.95rem;
            line-height: 1.7;
            color: #e5e7ff;
        }

        .app-modal-body pre {
            background: rgba(15, 10, 40, 0.6);
            padding: 1rem;
            border-radius: 12px;
            color: #f3f4ff;
            overflow-x: auto;
        }

        .app-modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
            padding: 1rem 1.5rem 1.5rem;
            background: rgba(30, 16, 70, 0.75);
        }

        .app-modal-footer .btn {
            min-width: 110px;
        }

        @media (max-width: 768px) {
            .navbar-brand {
                font-size: 1.25rem;
            }
            
            .navbar-brand img {
                height: 32px;
            }

            .dashboard-container {
                flex-direction: column;
            }

            .dashboard-sidebar {
                width: 100%;
                border-right: none;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            }

            .dashboard-main {
                padding: 1.5rem;
            }
        }
    </style>
    @yield('styles')
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="/" id="home-link">
                <img src="{{ asset('images/star-recruiting-logo.png') }}" alt="Star Recruiting Logo" id="logo-img" style="height: 40px; width: auto;" onerror="this.style.display='none'; document.getElementById('logo-text').style.display='inline';">
                <span id="logo-text" style="display: none;">star recruiting</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto" id="nav-auth-buttons">
                    <li class="nav-item" id="nav-login" style="display: none;">
                        <a class="nav-link" href="/login">Login</a>
                    </li>
                    <li class="nav-item" id="nav-register" style="display: none;">
                        <a class="nav-link" href="/register">Register</a>
                    </li>
                    <li class="nav-item" id="nav-settings" style="display: none;">
                        <a class="nav-link" href="#" id="settings-link">Settings</a>
                    </li>
                    <li class="nav-item" id="nav-logout" style="display: none;">
                        <a class="nav-link" href="#" onclick="handleLogout(); return false;">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div id="app-content">
        @yield('content')
    </div>

    <div id="app-modal-overlay" class="app-modal-overlay" aria-hidden="true">
        <div class="app-modal" role="dialog" aria-modal="true" aria-labelledby="app-modal-title">
            <div class="app-modal-header info" id="app-modal-header">
                <h5 id="app-modal-title" class="mb-0">Notification</h5>
                <button type="button" class="app-modal-close" aria-label="Close">&times;</button>
            </div>
            <div class="app-modal-body" id="app-modal-body"></div>
            <div class="app-modal-footer" id="app-modal-buttons"></div>
        </div>
    </div>
    <script>
        (function() {
            const overlay = document.getElementById('app-modal-overlay');
            if (!overlay) {
                return;
            }

            const modal = overlay.querySelector('.app-modal');
            const header = document.getElementById('app-modal-header');
            const titleEl = document.getElementById('app-modal-title');
            const bodyEl = document.getElementById('app-modal-body');
            const buttonsContainer = document.getElementById('app-modal-buttons');
            const closeBtn = overlay.querySelector('.app-modal-close');

            let currentResolve = null;
            let currentConfig = null;

            const VARIANT_CLASS = {
                success: 'success',
                danger: 'danger',
                warning: 'warning',
                info: 'info'
            };

            function clearButtons() {
                buttonsContainer.innerHTML = '';
            }

            function setVariant(variant) {
                const variantKey = VARIANT_CLASS[variant] ? variant : 'info';
                header.className = `app-modal-header ${variantKey}`;
            }

            function hideModal(result = null) {
                if (overlay.getAttribute('data-visible') !== 'true') {
                    return;
                }

                modal.classList.remove('show');
                overlay.setAttribute('data-visible', 'false');
                overlay.setAttribute('aria-hidden', 'true');
                setTimeout(() => {
                    overlay.style.display = 'none';
                }, 180);

                document.body.classList.remove('app-modal-open');

                const closeValue = (result === null || result === undefined) && currentConfig ? currentConfig.closeValue : result;

                if (typeof currentConfig?.onClose === 'function') {
                    currentConfig.onClose(closeValue);
                }

                if (currentResolve) {
                    currentResolve(closeValue);
                    currentResolve = null;
                }

                currentConfig = null;
            }

            function openModal(config, resolve) {
                const {
                    title = 'Notification',
                    message = '',
                    variant = 'info',
                    buttons = null,
                    onClose = null,
                    closeValue = null,
                    allowOutsideClose = true
                } = config || {};

                currentResolve = resolve || null;
                currentConfig = { onClose, closeValue, allowOutsideClose };

                titleEl.textContent = title;
                bodyEl.innerHTML = typeof message === 'string' ? message : '';
                setVariant(variant);

                clearButtons();

                const renderedButtons = Array.isArray(buttons) && buttons.length > 0
                    ? buttons
                    : [{ label: 'Close', variant: 'primary', value: closeValue }];

                renderedButtons.forEach((button) => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = `btn btn-${button.variant || 'primary'}`;
                    btn.textContent = button.label || 'Close';
                    btn.addEventListener('click', () => {
                        if (typeof button.onClick === 'function') {
                            button.onClick();
                        }
                        if (button.dismiss !== false) {
                            hideModal(button.value !== undefined ? button.value : closeValue);
                        }
                    });
                    buttonsContainer.appendChild(btn);
                });

                overlay.style.display = 'flex';
                overlay.setAttribute('data-visible', 'true');
                overlay.setAttribute('aria-hidden', 'false');
                document.body.classList.add('app-modal-open');

                requestAnimationFrame(() => {
                    modal.classList.add('show');
                });
            }

            function handleOverlayClick(event) {
                if (event.target === overlay && currentConfig?.allowOutsideClose !== false) {
                    hideModal(currentConfig?.closeValue ?? null);
                }
            }

            function handleKeydown(event) {
                if (event.key === 'Escape' && overlay.getAttribute('data-visible') === 'true') {
                    hideModal(currentConfig?.closeValue ?? null);
                }
            }

            closeBtn.addEventListener('click', () => hideModal(currentConfig?.closeValue ?? null));
            overlay.addEventListener('click', handleOverlayClick);
            document.addEventListener('keydown', handleKeydown);

            window.showAppModal = function(config) {
                return new Promise((resolve) => openModal(config, resolve));
            };

            window.hideAppModal = hideModal;

            window.showAppConfirm = function(options = {}) {
                const {
                    title = 'Confirm Action',
                    message = '',
                    confirmLabel = 'Confirm',
                    cancelLabel = 'Cancel',
                    confirmVariant = 'danger',
                    variant = 'info'
                } = options;

                return new Promise((resolve) => {
                    openModal({
                        title,
                        message,
                        variant,
                        closeValue: false,
                        buttons: [
                            { label: cancelLabel, variant: 'secondary', value: false },
                            { label: confirmLabel, variant: confirmVariant, value: true }
                        ],
                        onClose: (value) => resolve(Boolean(value))
                    });
                });
            };

            window.showAppMessage = function({ title = 'Notice', message = '', variant = 'info' } = {}) {
                return window.showAppModal({ title, message, variant });
            };
        })();
    </script>
    
    <script>
        // Add dashboard-page class to body if using dashboard layout
        document.addEventListener('DOMContentLoaded', function() {
            const dashboardContainer = document.querySelector('.dashboard-container');
            if (dashboardContainer) {
                document.body.classList.add('dashboard-page');
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Check authentication status and update navbar
        function updateNavbar() {
            const token = localStorage.getItem('api_token');
            const userRole = localStorage.getItem('user_role');
            const loginItem = document.getElementById('nav-login');
            const registerItem = document.getElementById('nav-register');
            const settingsItem = document.getElementById('nav-settings');
            const settingsLink = document.getElementById('settings-link');
            const logoutItem = document.getElementById('nav-logout');
            const homeLink = document.getElementById('home-link');
            
            if (token) {
                // User is logged in - show logout and settings, hide login/register
                if (loginItem) loginItem.style.display = 'none';
                if (registerItem) registerItem.style.display = 'none';
                if (logoutItem) logoutItem.style.display = 'block';
                
                // Update home link based on role
                if (homeLink) {
                    if (userRole === 'candidate') {
                        homeLink.href = '/candidate/home';
                    } else if (userRole === 'recruiter') {
                        homeLink.href = '/recruiter/home';
                    }
                }
                
                // Show settings link based on role
                if (settingsItem && settingsLink) {
                    if (userRole === 'candidate') {
                        settingsLink.href = '/candidate/settings';
                        settingsItem.style.display = 'block';
                    } else if (userRole === 'recruiter') {
                        settingsLink.href = '/recruiter/settings';
                        settingsItem.style.display = 'block';
                    } else {
                        settingsItem.style.display = 'none';
                    }
                }
            } else {
                // User is not logged in - show login/register, hide logout and settings
                if (loginItem) loginItem.style.display = 'block';
                if (registerItem) registerItem.style.display = 'block';
                if (logoutItem) logoutItem.style.display = 'none';
                if (settingsItem) settingsItem.style.display = 'none';
                
                // Set home link to welcome page
                if (homeLink) {
                    homeLink.href = '/';
                }
            }
        }
        
        async function handleLogout() {
            const confirmed = await showAppConfirm({
                title: 'Logout',
                message: 'Are you sure you want to logout?',
                confirmLabel: 'Logout',
                confirmVariant: 'danger',
                variant: 'warning'
            });

            if (confirmed) {
                localStorage.removeItem('api_token');
                localStorage.removeItem('user_role');
                window.location.href = '/';
            }
        }
        
        // Update navbar on page load
        updateNavbar();
    </script>
    @yield('scripts')
</body>
</html>


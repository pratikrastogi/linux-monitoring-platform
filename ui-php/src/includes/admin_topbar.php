<?php
// Compact admin top bar for alerts and logout
?>
<style>
.app-shell {
    margin-left: 0 !important;
    background: var(--bg-light);
    min-height: 100vh;
    padding: 90px 24px 40px;
}

@media (max-width: 992px) {
    .app-shell {
        padding: 90px 16px 32px;
    }
}

.compact-topbar {
    position: fixed;
    top: 18px;
    right: 18px;
    display: flex;
    gap: 10px;
    z-index: 1101;
}

.compact-topbar .top-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 14px;
    background: #ffffff;
    color: #2c3e50;
    border: 1px solid rgba(102, 126, 234, 0.25);
    border-radius: 12px;
    text-decoration: none;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
    font-weight: 600;
    transition: all 0.25s ease;
}

.compact-topbar .top-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 28px rgba(0, 0, 0, 0.12);
}

.compact-topbar .top-btn i {
    color: #667eea;
}

.compact-topbar .top-btn.danger {
    background: linear-gradient(135deg, #ff6b6b, #ff4757);
    color: #fff;
    border: none;
}

.compact-topbar .top-btn.danger i {
    color: #fff;
}

.compact-topbar .top-btn.ghost {
    background: #f4f7fc;
    color: #667eea;
}

.compact-topbar .pill {
    min-width: 26px;
    padding: 4px 6px;
    background: #ff6b6b;
    color: #fff;
    border-radius: 999px;
    font-size: 0.85rem;
    font-weight: 700;
    text-align: center;
    line-height: 1;
}

@media (max-width: 576px) {
    .compact-topbar {
        right: 12px;
        top: 12px;
    }

    .compact-topbar .top-btn {
        padding: 9px 10px;
        gap: 6px;
    }

    .compact-topbar .top-btn .label {
        display: none;
    }
}
</style>

<div class="compact-topbar">
    <a href="/alerts.php" class="top-btn" title="View alerts">
        <i class="fas fa-bell"></i>
        <span class="pill" id="compactAlertCount">0</span>
    </a>
    <a href="/profile.php" class="top-btn ghost" title="My profile">
        <i class="fas fa-user"></i>
    </a>
    <a href="/logout.php" class="top-btn danger" title="Logout">
        <i class="fas fa-sign-out-alt"></i>
        <span class="label">Logout</span>
    </a>
</div>

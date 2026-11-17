<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="theme-color" content="#0d2f2a">
    <title>MemberLink SACCO</title>
    <link rel="stylesheet" href="/assets/style.css?v=5">
</head>
<body class="app-body">
<?php if (!empty($is_login)): ?>
    <?php echo $content ?? ''; ?>
<?php else: ?>
    <div class="app-shell">
        <aside class="sidebar">
            <div class="brand">
                <div class="brand-mark">ML</div>
                <div class="brand-copy">
                    <span>MemberLink</span>
                    <strong>SACCO</strong>
                </div>
            </div>

            <nav class="side-nav" aria-label="Main navigation">
                <a href="?page=dashboard">Dashboard</a>
                <a href="?page=members">Members</a>
                <a href="?page=savings">Savings</a>
                <a href="?page=loans">Loans</a>
                <a href="?page=reports">Reports</a>
                <?php if (($user['role'] ?? '') === 'admin'): ?>
                    <a href="?page=users">Users</a>
                <?php endif; ?>
                <a href="?page=backup">Backup</a>
                <a href="?page=logout">Logout</a>
            </nav>

            <div class="profile-card">
                <span>Logged in as</span>
                <strong><?php echo htmlspecialchars(($user['name'] ?? 'User')); ?></strong>
                <small><?php echo htmlspecialchars(($user['role'] ?? 'staff')); ?></small>
            </div>
        </aside>

        <div class="main-panel">
            <header class="topbar">
                <div>
                    <span class="eyebrow">Operations Center</span>
                    <h1>MemberLink SACCO</h1>
                </div>
                <div class="user-badge">Welcome, <?php echo htmlspecialchars($user['name'] ?? 'User'); ?></div>
            </header>

            <main>
                <?php echo $content ?? ''; ?>
            </main>
        </div>
    </div>
<?php endif; ?>
</body>
</html>

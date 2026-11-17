<?php
ob_start();
?>
<div class="login-wrapper">
    <div class="login-card">
        <div class="logo">ML</div>
        <h2>Staff Login</h2>
        <p class="login-subtitle">MemberLink SACCO Access Portal</p>

        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="post" action="?page=login" class="login-form">
            <input type="email" name="email" placeholder="Email address" required autocomplete="email">
            <input type="password" name="password" placeholder="Password" required autocomplete="current-password">
            <button type="submit">Login</button>
        </form>

        <div class="login-links">
            <a href="?page=forgot_password">Forgot password?</a>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';

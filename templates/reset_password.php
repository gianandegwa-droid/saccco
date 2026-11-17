<?php
ob_start();
?>
<div class="login-wrapper">
    <div class="login-card">
        <div class="logo">ML</div>
        <h2>Create New Password</h2>
        <p class="login-subtitle">Set a new password for <?php echo htmlspecialchars($email ?? 'your account'); ?></p>

        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="post" action="?page=reset_password" class="login-form">
            <input type="password" name="new_password" placeholder="New password (at least 8 chars)" required minlength="8" autocomplete="new-password">
            <input type="password" name="confirm_password" placeholder="Confirm new password" required minlength="8" autocomplete="new-password">
            <button type="submit">Update Password</button>
        </form>

        <div class="login-links">
            <a href="?page=login">Back to login</a>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';

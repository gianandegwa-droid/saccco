<?php
ob_start();
?>
<div class="login-wrapper">
    <div class="login-card">
        <div class="logo">ML</div>
        <h2>Reset Password</h2>
        <p class="login-subtitle">Verify your email to continue</p>

        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="post" action="?page=forgot_password" class="login-form">
            <input type="email" name="email" placeholder="Email address" required autocomplete="email">
            <button type="submit">Send Verification Code</button>
        </form>

        <div class="login-links">
            <a href="?page=login">Back to login</a>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';

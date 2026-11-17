<?php
ob_start();
?>
<div class="login-wrapper">
    <div class="login-card">
        <div class="logo">ML</div>
        <h2>Email Verification</h2>
        <p class="login-subtitle">Enter the 6-digit code sent to your email</p>

        <?php if (!empty($message)): ?>
            <div class="info-box"><?php echo htmlspecialchars($message); ?> <strong><?php echo htmlspecialchars($otp ?? ''); ?></strong></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="post" action="?page=verify_otp" class="login-form">
            <input type="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" placeholder="Email address" required autocomplete="email">
            <input type="text" name="otp" placeholder="6-digit security code" required pattern="[0-9]{6}" maxlength="6" inputmode="numeric">
            <button type="submit">Verify Code</button>
        </form>

        <div class="login-links">
            <a href="?page=login">Back to login</a>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';

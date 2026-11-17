<?php
ob_start();
$m = $member ?? [];
?>
<h2><?php echo empty($m) ? 'Add Member' : 'Edit Member' ?></h2>
<?php if (!empty($warning)): ?><div class="warning"><?php echo htmlspecialchars($warning); ?></div><?php endif; ?>
<form method="post" action="?page=member_form<?php echo !empty($m['id']) ? '&id='.$m['id']: '' ?>">
    <label>First name</label>
    <input name="first_name" value="<?php echo htmlspecialchars($m['first_name'] ?? '') ?>">
    <label>Last name</label>
    <input name="last_name" value="<?php echo htmlspecialchars($m['last_name'] ?? '') ?>">
    <label>National ID</label>
    <input name="national_id" value="<?php echo htmlspecialchars($m['national_id'] ?? '') ?>">
    <label>Phone</label>
    <input name="phone" value="<?php echo htmlspecialchars($m['phone'] ?? '') ?>">
    <label>Email</label>
    <input name="email" value="<?php echo htmlspecialchars($m['email'] ?? '') ?>">
    <label>Address</label>
    <input name="address" value="<?php echo htmlspecialchars($m['address'] ?? '') ?>">
    <label>Date of Birth</label>
    <input type="date" name="dob" value="<?php echo htmlspecialchars($m['dob'] ?? '') ?>">
    <button type="submit">Save</button>
</form>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';

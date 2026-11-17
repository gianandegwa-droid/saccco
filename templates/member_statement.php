<?php
ob_start();
$member = $member ?? [];
$account = $account ?? [];
$loans = $loans ?? [];
$payments = $payments ?? [];
?>
<h2>Member Account Statement</h2>
<div class="statement-actions">
    <a class="btn" href="?page=member_detail&id=<?php echo (int)($member['id'] ?? 0); ?>">Back to Profile</a>
    <button class="btn" type="button" onclick="window.print()">Print Statement</button>
</div>

<div class="member-summary">
    <div><strong>Member:</strong> <?php echo htmlspecialchars(($member['first_name'] ?? '') . ' ' . ($member['last_name'] ?? '')); ?></div>
    <div><strong>National ID:</strong> <?php echo htmlspecialchars($member['national_id'] ?? ''); ?></div>
    <div><strong>Phone:</strong> <?php echo htmlspecialchars($member['phone'] ?? ''); ?></div>
    <div><strong>Email:</strong> <?php echo htmlspecialchars($member['email'] ?? ''); ?></div>
    <div><strong>Address:</strong> <?php echo htmlspecialchars($member['address'] ?? ''); ?></div>
    <div><strong>Savings Balance:</strong> KES <?php echo number_format((float)($account['savings_balance'] ?? 0), 2); ?></div>
</div>

<h3>Loan Summary</h3>
<table>
    <thead>
        <tr><th>Loan ID</th><th>Principal</th><th>Balance</th><th>Status</th></tr>
    </thead>
    <tbody>
        <?php foreach ($loans as $loan): ?>
            <tr>
                <td><?php echo (int)($loan['id'] ?? 0); ?></td>
                <td>KES <?php echo number_format((float)($loan['principal'] ?? 0), 2); ?></td>
                <td>KES <?php echo number_format((float)($loan['balance'] ?? 0), 2); ?></td>
                <td><?php echo htmlspecialchars($loan['status'] ?? 'approved'); ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<h3>Repayment History</h3>
<table>
    <thead>
        <tr><th>Loan ID</th><th>Amount</th><th>Date</th></tr>
    </thead>
    <tbody>
        <?php foreach ($payments as $payment): ?>
            <tr>
                <td><?php echo (int)($payment['loan_id'] ?? 0); ?></td>
                <td>KES <?php echo number_format((float)($payment['amount'] ?? 0), 2); ?></td>
                <td><?php echo htmlspecialchars($payment['paid_at'] ?? ''); ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';

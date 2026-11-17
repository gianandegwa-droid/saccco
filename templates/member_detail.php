<?php
ob_start();
$member = $member ?? [];
$account = $account ?? [];
?>
<h2>Member Profile</h2>
<p><a href="?page=members">&larr; Back to Members</a></p>

<div class="member-summary">
    <div>
        <strong>Name:</strong> <?php echo htmlspecialchars(($member['first_name'] ?? '') . ' ' . ($member['last_name'] ?? '')); ?>
    </div>
    <div><strong>National ID:</strong> <?php echo htmlspecialchars($member['national_id'] ?? ''); ?></div>
    <div><strong>Phone:</strong> <?php echo htmlspecialchars($member['phone'] ?? ''); ?></div>
    <div><strong>Email:</strong> <?php echo htmlspecialchars($member['email'] ?? ''); ?></div>
    <div><strong>Address:</strong> <?php echo htmlspecialchars($member['address'] ?? ''); ?></div>
    <div><strong>Date of Birth:</strong> <?php echo htmlspecialchars($member['dob'] ?? ''); ?></div>
</div>

<div class="stats-grid">
    <div class="stat-card success">
        <span>Savings Balance</span>
        <strong>KES <?php echo number_format((float)($account['savings_balance'] ?? 0), 2); ?></strong>
    </div>
    <div class="stat-card warning">
        <span>Outstanding Loans</span>
        <strong>KES <?php echo number_format(array_sum(array_map(fn($row) => (float)($row['balance'] ?? 0), $loans ?? [])), 2); ?></strong>
    </div>
</div>

<h3>Loan Accounts</h3>
<table>
    <thead>
        <tr>
            <th>Loan ID</th>
            <th>Principal</th>
            <th>Balance</th>
            <th>Status</th>
            <th>Record Payment</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach (($loans ?? []) as $loan): ?>
            <tr>
                <td><?php echo (int)($loan['id'] ?? 0); ?></td>
                <td>KES <?php echo number_format((float)($loan['principal'] ?? 0), 2); ?></td>
                <td>KES <?php echo number_format((float)($loan['balance'] ?? 0), 2); ?></td>
                <td><?php echo htmlspecialchars($loan['status'] ?? 'approved'); ?></td>
                <td>
                    <form method="post" action="?page=member_detail&id=<?php echo (int)($member['id'] ?? 0); ?>" class="inline-form">
                        <input type="hidden" name="action" value="repay">
                        <input type="hidden" name="loan_id" value="<?php echo (int)($loan['id'] ?? 0); ?>">
                        <input type="number" name="amount" min="0" step="0.01" placeholder="5000" required>
                        <button type="submit" class="btn small">Pay</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<h3>Loan Payment History</h3>
<table>
    <thead>
        <tr>
            <th>Loan ID</th>
            <th>Amount</th>
            <th>Date</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach (($payments ?? []) as $payment): ?>
            <tr>
                <td><?php echo (int)($payment['loan_id'] ?? 0); ?></td>
                <td>KES <?php echo number_format((float)($payment['amount'] ?? 0), 2); ?></td>
                <td><?php echo htmlspecialchars($payment['paid_at'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($payment['status'] ?? 'active'); ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="statement-actions">
    <a class="btn" href="?page=member_statement&id=<?php echo (int)($member['id'] ?? 0); ?>">Print Statement</a>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';

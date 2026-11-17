<?php
ob_start();
?>
<h2>Loan / Credit Ledger</h2>
<p>Issue credit to members and review outstanding balances due to the SACCO.</p>

<form method="post" action="?page=loans" class="finance-form">
    <label>
        Member
        <select name="member_id" required>
            <option value="">Select member</option>
            <?php foreach ($members as $member): ?>
                <option value="<?php echo (int)$member['id']; ?>"><?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>
        Amount (KES)
        <input type="number" name="amount" min="0" step="0.01" placeholder="25000" required>
    </label>
    <label>
        Loan status
        <select name="status">
            <option value="approved">Approved</option>
            <option value="active">Active</option>
            <option value="pending">Pending</option>
            <option value="closed">Closed</option>
        </select>
    </label>
    <button type="submit" class="btn">Offer Credit</button>
</form>

<h3>Member Credit Summary</h3>
<table>
    <thead>
        <tr>
            <th>Member</th>
            <th>Principal</th>
            <th>Balance</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($loans as $entry): ?>
            <tr>
                <td><?php echo htmlspecialchars($entry['first_name'] . ' ' . $entry['last_name']); ?></td>
                <td>KES <?php echo number_format((float)($entry['principal'] ?? 0), 2); ?></td>
                <td>KES <?php echo number_format((float)($entry['balance'] ?? 0), 2); ?></td>
                <td><?php echo htmlspecialchars($entry['status'] ?? 'approved'); ?></td>
                <td>
                    <form method="post" action="?page=loans" class="inline-form">
                        <input type="hidden" name="loan_id" value="<?php echo (int)($entry['id'] ?? 0); ?>">
                        <select name="status">
                            <option value="pending" <?php echo (($entry['status'] ?? '') === 'pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="approved" <?php echo (($entry['status'] ?? '') === 'approved') ? 'selected' : ''; ?>>Approved</option>
                            <option value="active" <?php echo (($entry['status'] ?? '') === 'active') ? 'selected' : ''; ?>>Active</option>
                            <option value="closed" <?php echo (($entry['status'] ?? '') === 'closed') ? 'selected' : ''; ?>>Closed</option>
                        </select>
                        <button type="submit" class="btn small">Update</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <td><strong>Total</strong></td>
            <td colspan="2"><strong>KES <?php echo number_format(array_sum(array_map(fn($row) => (float)($row['balance'] ?? 0), $loans)), 2); ?></strong></td>
            <td></td>
            <td></td>
        </tr>
    </tfoot>
</table>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';

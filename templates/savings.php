<?php
ob_start();
?>
<h2>Savings Ledger</h2>
<p>Record deposits and review each member's savings balance with the SACCO.</p>

<form method="post" action="?page=savings" class="finance-form">
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
        Deposit Amount (KES)
        <input type="number" name="amount" min="0" step="0.01" placeholder="5000" required>
    </label>
    <button type="submit" class="btn">Add Savings</button>
</form>

<h3>Member Savings Summary</h3>
<table>
    <thead>
        <tr>
            <th>Member</th>
            <th>Saved Amount</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($savings as $entry): ?>
            <tr>
                <td><?php echo htmlspecialchars($entry['first_name'] . ' ' . $entry['last_name']); ?></td>
                <td>KES <?php echo number_format((float)($entry['savings_balance'] ?? 0), 2); ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <td><strong>Total</strong></td>
            <td><strong>KES <?php echo number_format(array_sum(array_map(fn($row) => (float)($row['savings_balance'] ?? 0), $savings)), 2); ?></strong></td>
        </tr>
    </tfoot>
</table>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';

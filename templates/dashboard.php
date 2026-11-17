<?php
ob_start();
$dashboardTitle = (($user['role'] ?? '') === 'admin') ? 'Admin Dashboard' : 'Staff Dashboard';
?>
<h2><?php echo htmlspecialchars($dashboardTitle); ?></h2>
<p>Manage member records, savings, loan applications, and operational reports for MemberLink SACCO.</p>

<div class="stats-grid">
    <div class="stat-card">
        <span>Total Members</span>
        <strong><?php echo number_format((int)($memberCount ?? 0)); ?></strong>
    </div>
    <div class="stat-card success">
        <span>Total Savings</span>
        <strong>KES <?php echo number_format((float)($totalSavings ?? 0), 2); ?></strong>
    </div>
    <div class="stat-card warning">
        <span>Loan Balance</span>
        <strong>KES <?php echo number_format((float)($loanBalance ?? 0), 2); ?></strong>
    </div>
</div>

<div class="quick-links">
    <a class="btn" href="?page=members">View Members</a>
    <a class="btn" href="?page=member_form">Register Member</a>
    <a class="btn" href="?page=savings">Savings Ledger</a>
    <a class="btn" href="?page=loans">Loan Applications</a>
    <a class="btn" href="?page=reports">Reports</a>
    <a class="btn" href="?page=export_csv">Generate Report</a>
    <a class="btn" href="?page=backup">Backup Records</a>
    <a class="btn" href="?page=logout">Logout</a>
    <?php if (($user['role'] ?? '') === 'admin'): ?>
        <a class="btn" href="?page=users">Users</a>
    <?php endif; ?>
</div>

<div class="dashboard-panels">
    <section>
        <h3>Member Savings</h3>
        <table>
            <thead>
                <tr><th>Member</th><th>Saved Amount</th></tr>
            </thead>
            <tbody>
                <?php foreach (($memberSavings ?? []) as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')); ?></td>
                        <td>KES <?php echo number_format((float)($row['savings_balance'] ?? 0), 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <section>
        <h3>Member Credit</h3>
        <table>
            <thead>
                <tr><th>Member</th><th>Balance</th><th>Status</th></tr>
            </thead>
            <tbody>
                <?php foreach (($memberLoans ?? []) as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')); ?></td>
                        <td>KES <?php echo number_format((float)($row['balance'] ?? 0), 2); ?></td>
                        <td><?php echo htmlspecialchars($row['status'] ?? 'approved'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';


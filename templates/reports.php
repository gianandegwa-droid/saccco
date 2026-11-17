<?php
ob_start();
$maxSavings = (float)($maxSavings ?? 1);
?>
<h2>Operational Reports</h2>
<p>Monitor SACCO performance, member savings, and outstanding credit exposure.</p>

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
        <span>Total Loans</span>
        <strong>KES <?php echo number_format((float)($loanBalance ?? 0), 2); ?></strong>
    </div>
</div>

<div class="dashboard-panels">
    <section>
        <h3>Member Savings</h3>
        <?php foreach (($memberSavings ?? []) as $row): ?>
            <?php $barWidth = $maxSavings > 0 ? min(100, ((float)($row['savings_balance'] ?? 0) / $maxSavings) * 100) : 0; ?>
            <div class="bar-row">
                <div class="bar-label"><?php echo htmlspecialchars(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')); ?></div>
                <div class="bar-track"><div class="bar-fill" style="width: <?php echo $barWidth; ?>%"></div></div>
                <div class="bar-value">KES <?php echo number_format((float)($row['savings_balance'] ?? 0), 2); ?></div>
            </div>
        <?php endforeach; ?>
    </section>

    <section>
        <h3>Loan Exposure</h3>
        <?php foreach (($memberLoans ?? []) as $row): ?>
            <?php $loanAmount = (float)($row['balance'] ?? 0); ?>
            <div class="bar-row">
                <div class="bar-label"><?php echo htmlspecialchars(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')); ?></div>
                <div class="bar-track"><div class="bar-fill warning" style="width: <?php echo $loanBalance > 0 ? min(100, ($loanAmount / max(1, (float)$loanBalance)) * 100) : 0; ?>%"></div></div>
                <div class="bar-value">KES <?php echo number_format($loanAmount, 2); ?></div>
            </div>
        <?php endforeach; ?>
    </section>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';

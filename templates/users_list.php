<?php
ob_start();
?>
<h2>System Users</h2>
<p>Manage the SACCO staff and administrative access accounts.</p>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Created</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach (($users ?? []) as $row): ?>
            <tr>
                <td><?php echo (int)($row['id'] ?? 0); ?></td>
                <td><?php echo htmlspecialchars($row['name'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($row['email'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($row['role'] ?? 'teller'); ?></td>
                <td><?php echo htmlspecialchars($row['created_at'] ?? ''); ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';

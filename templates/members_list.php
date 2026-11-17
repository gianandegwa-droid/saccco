<?php
ob_start();
?>
<h2>Members</h2>
<form method="get" action="">
    <input type="hidden" name="page" value="members">
    <input type="text" name="q" placeholder="Search name, ID or phone" value="<?php echo htmlspecialchars($q ?? '') ?>">
    <button type="submit">Search</button>
    <a class="btn" href="?page=member_form">Add New</a>
    <a class="btn" href="?page=export_csv">Export CSV</a>
</form>
<table>
    <thead><tr><th>ID</th><th>Name</th><th>National ID</th><th>Phone</th><th>Email</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach($members as $m): ?>
        <tr>
            <td><?php echo $m['id'] ?></td>
            <td><?php echo htmlspecialchars($m['first_name'] . ' ' . $m['last_name']) ?></td>
            <td><?php echo htmlspecialchars($m['national_id']) ?></td>
            <td><?php echo htmlspecialchars($m['phone']) ?></td>
            <td><?php echo htmlspecialchars($m['email']) ?></td>
            <td>
                <a href="?page=member_detail&id=<?php echo $m['id'] ?>">View</a> |
                <a href="?page=member_form&id=<?php echo $m['id'] ?>">Edit</a> |
                <a href="?page=member_delete&id=<?php echo $m['id'] ?>" onclick="return confirm('Delete?')">Delete</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';

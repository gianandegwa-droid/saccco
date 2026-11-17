<?php
// CLI test runner for basic DB and model operations
require_once __DIR__ . '/../init_db.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/MemberModel.php';

use App\MemberModel;

echo "Running tests...\n";
$m = new MemberModel();

// Clean up any test member with national_id TEST123
$pdo = \App\Database::getInstance();
$pdo->exec("DELETE FROM members WHERE national_id = 'TEST123'");

// Create
$ok = $m->create(['first_name'=>'Test','last_name'=>'User','national_id'=>'TEST123','phone'=>'0711000000','email'=>'test@example.com','address'=>'Nairobi','dob'=>'1990-01-01']);
if (!$ok) { echo "Create failed\n"; exit(1); }
echo "Create passed\n";

// Duplicate detection
$dup = $m->findDuplicate('TEST123','0711000000','test@example.com');
if (!$dup) { echo "Duplicate detection failed\n"; exit(1); }
echo "Duplicate detection passed\n";

// Find
$rows = $m->search('Test');
if (count($rows) < 1) { echo "Search failed\n"; exit(1); }
echo "Search passed\n";

$id = $rows[0]['id'];

// Update
$m->update($id, ['first_name'=>'Updated','last_name'=>'User','national_id'=>'TEST123','phone'=>'0711000000','email'=>'test@example.com','address'=>'Nairobi','dob'=>'1990-01-01']);
$r = $m->find($id);
if ($r['first_name'] !== 'Updated') { echo "Update failed\n"; exit(1); }
echo "Update passed\n";

// Delete
$m->delete($id);
$r2 = $m->find($id);
if ($r2) { echo "Delete failed\n"; exit(1); }
echo "Delete passed\n";

echo "All tests passed.\n";
exit(0);

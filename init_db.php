<?php
// init_db.php - create SQLite DB and seed admin
$dir = __DIR__ . '/data';
if (!is_dir($dir)) mkdir($dir, 0755, true);
$dbFile = $dir . '/database.sqlite';
$db = new PDO('sqlite:' . $dbFile);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$db->exec("CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL,
    role TEXT NOT NULL DEFAULT 'teller',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

$db->exec("CREATE TABLE IF NOT EXISTS members (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    first_name TEXT,
    last_name TEXT,
    national_id TEXT UNIQUE,
    phone TEXT,
    email TEXT,
    address TEXT,
    dob TEXT,
    joined_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

$db->exec("CREATE TABLE IF NOT EXISTS member_accounts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    member_id INTEGER UNIQUE NOT NULL,
    savings_balance REAL DEFAULT 0,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(member_id) REFERENCES members(id)
)");

$db->exec("CREATE TABLE IF NOT EXISTS member_loans (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    member_id INTEGER NOT NULL,
    principal REAL NOT NULL,
    balance REAL NOT NULL,
    status TEXT NOT NULL DEFAULT 'approved',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(member_id) REFERENCES members(id)
)");

// Add reset-code columns to users table if they don't exist
$columns = $db->query("PRAGMA table_info(users)")->fetchAll(PDO::FETCH_ASSOC);
$existingColumns = [];
foreach ($columns as $column) {
    $existingColumns[] = $column['name'];
}

if (!in_array('password_reset_code', $existingColumns, true)) {
    $db->exec('ALTER TABLE users ADD COLUMN password_reset_code TEXT');
}

if (!in_array('password_reset_expires_at', $existingColumns, true)) {
    $db->exec('ALTER TABLE users ADD COLUMN password_reset_expires_at DATETIME');
}

// Seed admin user
$stmt = $db->prepare('SELECT COUNT(*) FROM users');
$stmt->execute();
$count = $stmt->fetchColumn();
if ($count == 0) {
    $password = password_hash('Admin@123', PASSWORD_DEFAULT);
    $ins = $db->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)');
    $ins->execute(['Admin', 'admin@memberlink.local', $password, 'admin']);
    echo "Created admin user: admin@memberlink.local / Admin@123\n";
} else {
    echo "Users already seeded.\n";
}

// Ensure each member has a savings account and some sample amounts for demo
$members = $db->query('SELECT id FROM members')->fetchAll(PDO::FETCH_ASSOC);
foreach ($members as $member) {
    $db->exec("INSERT OR IGNORE INTO member_accounts (member_id, savings_balance, updated_at) VALUES (" . (int)$member['id'] . ", 5000, datetime('now'))");
}

$loanRows = [
    ['member_id' => 1, 'principal' => 25000, 'balance' => 18000, 'status' => 'active'],
    ['member_id' => 2, 'principal' => 40000, 'balance' => 30000, 'status' => 'active'],
    ['member_id' => 3, 'principal' => 30000, 'balance' => 30000, 'status' => 'approved'],
];
foreach ($loanRows as $row) {
    $db->prepare('INSERT OR IGNORE INTO member_loans (member_id, principal, balance, status) VALUES (?, ?, ?, ?)')
       ->execute([$row['member_id'], $row['principal'], $row['balance'], $row['status']]);
}

echo "Database initialized at: $dbFile\n";

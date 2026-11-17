<?php
namespace App;

use PDO;

class Database {
    private static $instance;
    private $pdo;

    private function __construct()
    {
        $dir = __DIR__ . '/../data';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $dbFile = $dir . '/database.sqlite';
        $this->pdo = new PDO('sqlite:' . $dbFile);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->initializeSchema();
    }

    private function initializeSchema()
    {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            role TEXT NOT NULL DEFAULT 'teller',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        $columns = $this->pdo->query("PRAGMA table_info(users)")->fetchAll(PDO::FETCH_ASSOC);
        $existingColumns = [];
        foreach ($columns as $column) {
            $existingColumns[] = $column['name'];
        }

        if (!in_array('password_reset_code', $existingColumns, true)) {
            $this->pdo->exec('ALTER TABLE users ADD COLUMN password_reset_code TEXT');
        }

        if (!in_array('password_reset_expires_at', $existingColumns, true)) {
            $this->pdo->exec('ALTER TABLE users ADD COLUMN password_reset_expires_at DATETIME');
        }

        $this->pdo->exec("CREATE TABLE IF NOT EXISTS members (
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

        $this->pdo->exec("CREATE TABLE IF NOT EXISTS member_accounts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            member_id INTEGER UNIQUE NOT NULL,
            savings_balance REAL DEFAULT 0,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY(member_id) REFERENCES members(id)
        )");

        $this->pdo->exec("CREATE TABLE IF NOT EXISTS member_loans (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            member_id INTEGER NOT NULL,
            principal REAL NOT NULL,
            balance REAL NOT NULL,
            status TEXT NOT NULL DEFAULT 'approved',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY(member_id) REFERENCES members(id)
        )");

        $this->pdo->exec("CREATE TABLE IF NOT EXISTS member_loan_payments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            loan_id INTEGER NOT NULL,
            amount REAL NOT NULL,
            paid_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY(loan_id) REFERENCES member_loans(id)
        )");

        $userCount = $this->pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
        if ((int)$userCount === 0) {
            $this->pdo->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)')
                ->execute(['Admin', 'admin@memberlink.local', password_hash('Admin@123', PASSWORD_DEFAULT), 'admin']);
            $this->pdo->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)')
                ->execute(['Teller', 'teller@memberlink.local', password_hash('Teller@123', PASSWORD_DEFAULT), 'teller']);
        }

        $members = $this->pdo->query('SELECT id FROM members')->fetchAll(PDO::FETCH_ASSOC);
        foreach ($members as $member) {
            $this->pdo->exec("INSERT OR IGNORE INTO member_accounts (member_id, savings_balance, updated_at) VALUES (" . (int)$member['id'] . ", 5000, datetime('now'))");
        }

        $loanCount = $this->pdo->query('SELECT COUNT(*) FROM member_loans')->fetchColumn();
        if ((int)$loanCount === 0) {
            $loanRows = [
                ['member_id' => 1, 'principal' => 25000, 'balance' => 18000, 'status' => 'active'],
                ['member_id' => 2, 'principal' => 40000, 'balance' => 30000, 'status' => 'active'],
                ['member_id' => 3, 'principal' => 30000, 'balance' => 30000, 'status' => 'approved'],
            ];

            foreach ($loanRows as $row) {
                $this->pdo->prepare('INSERT INTO member_loans (member_id, principal, balance, status) VALUES (?, ?, ?, ?)')
                    ->execute([$row['member_id'], $row['principal'], $row['balance'], $row['status']]);
            }
        }

        /*
        NOTE:
        We intentionally do not seed demo loans on every app boot because the member_loans
        table has no unique member_id constraint. Repeating the seeded inserts would make the
        reported loan balance grow each time the app reconnects or the user logs in again.
        */
    }

    public static function getInstance()
    {
        if (!self::$instance) self::$instance = new Database();
        return self::$instance->pdo;
    }
}

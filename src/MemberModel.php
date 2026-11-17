<?php
namespace App;

class MemberModel {
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }
    public function all()
    {
        $stmt = $this->pdo->query('SELECT * FROM members ORDER BY joined_at DESC');
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function find($id)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM members WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function search($q)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM members WHERE first_name LIKE ? OR last_name LIKE ? OR national_id LIKE ? OR phone LIKE ? ORDER BY joined_at DESC');
        $like = "%$q%";
        $stmt->execute([$like,$like,$like,$like]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $stmt = $this->pdo->prepare('INSERT INTO members (first_name,last_name,national_id,phone,email,address,dob) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $result = $stmt->execute([
            $data['first_name'] ?? null,
            $data['last_name'] ?? null,
            $data['national_id'] ?? null,
            $data['phone'] ?? null,
            $data['email'] ?? null,
            $data['address'] ?? null,
            $data['dob'] ?? null
        ]);

        if ($result) {
            $memberId = (int) $this->pdo->lastInsertId();
            $this->pdo->prepare('INSERT OR IGNORE INTO member_accounts (member_id, savings_balance, updated_at) VALUES (?, 0, datetime("now"))')
                ->execute([$memberId]);
            return $memberId;
        }

        return false;
    }


    public function update($id, $data)
    {
        $stmt = $this->pdo->prepare('UPDATE members SET first_name=?, last_name=?, national_id=?, phone=?, email=?, address=?, dob=? WHERE id=?');
        return $stmt->execute([
            $data['first_name'] ?? null,
            $data['last_name'] ?? null,
            $data['national_id'] ?? null,
            $data['phone'] ?? null,
            $data['email'] ?? null,
            $data['address'] ?? null,
            $data['dob'] ?? null,
            $id
        ]);
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare('DELETE FROM members WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function findDuplicate($national_id, $phone, $email)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM members WHERE national_id = ? OR phone = ? OR email = ? LIMIT 1');
        $stmt->execute([$national_id, $phone, $email]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function savingsSummary()
    {
        $stmt = $this->pdo->query('SELECT m.id, m.first_name, m.last_name, COALESCE(a.savings_balance, 0) AS savings_balance FROM members m LEFT JOIN member_accounts a ON a.member_id = m.id ORDER BY m.first_name');
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function loanSummary()
    {
        $stmt = $this->pdo->query('SELECT l.id AS id, l.member_id, m.first_name, m.last_name, l.principal, l.balance, l.status FROM member_loans l JOIN members m ON m.id = l.member_id ORDER BY l.created_at DESC');
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function addSavings($memberId, $amount)
    {
        $stmt = $this->pdo->prepare('INSERT INTO member_accounts (member_id, savings_balance, updated_at) VALUES (?, ?, datetime("now")) ON CONFLICT(member_id) DO UPDATE SET savings_balance = savings_balance + excluded.savings_balance, updated_at = datetime("now")');
        return $stmt->execute([(int)$memberId, (float)$amount]);
    }

    public function addLoan($memberId, $amount, $status = 'approved')
    {
        $stmt = $this->pdo->prepare('INSERT INTO member_loans (member_id, principal, balance, status) VALUES (?, ?, ?, ?)');
        return $stmt->execute([(int)$memberId, (float)$amount, (float)$amount, $status]);
    }

    public function totalSavingsValue()
    {
        $stmt = $this->pdo->query('SELECT COALESCE(SUM(savings_balance), 0) AS total FROM member_accounts');
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (float)($row['total'] ?? 0);
    }

    public function totalLoanBalance()
    {
        $stmt = $this->pdo->query('SELECT COALESCE(SUM(balance), 0) AS total FROM member_loans');
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (float)($row['total'] ?? 0);
    }
    public function updateLoanStatus($loanId, $status)
    {
        $status = in_array($status, ['pending', 'approved', 'active', 'closed'], true) ? $status : 'approved';
        $stmt = $this->pdo->prepare('UPDATE member_loans SET status = ? WHERE id = ?');
        return $stmt->execute([$status, (int)$loanId]);
    }

    public function allLoans()
    {
        $stmt = $this->pdo->query('SELECT l.id, l.member_id, m.first_name, m.last_name, l.principal, l.balance, l.status, l.created_at FROM member_loans l JOIN members m ON m.id = l.member_id ORDER BY l.created_at DESC');
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    public function memberAccount($memberId)
    {
        $stmt = $this->pdo->prepare('SELECT member_id, savings_balance, updated_at FROM member_accounts WHERE member_id = ? LIMIT 1');
        $stmt->execute([(int)$memberId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function memberLoans($memberId)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM member_loans WHERE member_id = ? ORDER BY created_at DESC');
        $stmt->execute([(int)$memberId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function memberPaymentHistory($memberId)
    {
        $stmt = $this->pdo->prepare('SELECT p.id, p.amount, p.paid_at, l.id AS loan_id, l.status FROM member_loan_payments p JOIN member_loans l ON l.id = p.loan_id WHERE l.member_id = ? ORDER BY p.paid_at DESC');
        $stmt->execute([(int)$memberId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function repayLoan($loanId, $amount)
    {
        $amount = (float)$amount;
        if ($amount <= 0) {
            return false;
        }

        $loanStmt = $this->pdo->prepare('SELECT id, balance FROM member_loans WHERE id = ? LIMIT 1');
        $loanStmt->execute([(int)$loanId]);
        $loan = $loanStmt->fetch(\PDO::FETCH_ASSOC);
        if (!$loan) {
            return false;
        }

        $remaining = max(0, (float)($loan['balance'] ?? 0) - $amount);
        $status = $remaining <= 0 ? 'closed' : 'active';

        $this->pdo->prepare('UPDATE member_loans SET balance = ?, status = ? WHERE id = ?')
            ->execute([$remaining, $status, (int)$loanId]);

        $this->pdo->prepare('INSERT INTO member_loan_payments (loan_id, amount, paid_at) VALUES (?, ?, datetime("now"))')
            ->execute([(int)$loanId, $amount]);

        return true;
    }
}

<?php
namespace App;

class Auth {
    public static function attempt($email, $password)
    {
        $email = trim(strtolower((string) $email));
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE lower(email) = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role']
            ];
            return true;
        }
        return false;
    }

    public static function user()
    {
        return $_SESSION['user'] ?? null;
    }

    public static function check()
    {
        return isset($_SESSION['user']);
    }

    public static function logout()
    {
        unset($_SESSION['user']);
        session_destroy();
    }

    public static function userExists($email)
    {
        $email = trim(strtolower($email));
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        return (bool) $stmt->fetch();
    }

    public static function createResetCode($email)
    {
        $email = trim(strtolower($email));
        if (!self::userExists($email)) {
            return null;
        }

        $code = (string) random_int(100000, 999999);
        $expiresAt = (new \DateTime('+15 minutes'))->format('Y-m-d H:i:s');
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('UPDATE users SET password_reset_code = ?, password_reset_expires_at = ? WHERE email = ?');
        $stmt->execute([password_hash($code, PASSWORD_DEFAULT), $expiresAt, $email]);

        return $code;
    }

    public static function verifyResetCode($email, $code)
    {
        $email = trim(strtolower($email));
        $code = trim((string) $code);
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('SELECT password_reset_code, password_reset_expires_at FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row || empty($row['password_reset_code']) || empty($row['password_reset_expires_at'])) {
            return false;
        }

        $expiresAt = new \DateTime($row['password_reset_expires_at']);
        if ((new \DateTime()) > $expiresAt) {
            self::clearResetCode($email);
            return false;
        }

        return password_verify($code, $row['password_reset_code']);
    }

    public static function clearResetCode($email)
    {
        $email = trim(strtolower($email));
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('UPDATE users SET password_reset_code = NULL, password_reset_expires_at = NULL WHERE email = ?');
        $stmt->execute([$email]);
    }

    public static function updatePassword($email, $newPassword)
    {
        $email = trim(strtolower($email));
        $newPassword = (string) $newPassword;

        if (strlen($newPassword) < 8) {
            return false;
        }

        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('UPDATE users SET password = ?, password_reset_code = NULL, password_reset_expires_at = NULL WHERE email = ?');
        $stmt->execute([password_hash($newPassword, PASSWORD_DEFAULT), $email]);

        return $stmt->rowCount() > 0;
    }

    public static function isAdmin()
    {
        $user = self::user();
        return ($user['role'] ?? '') === 'admin';
    }

    public static function isStaff()
    {
        $user = self::user();
        $role = $user['role'] ?? '';
        return in_array($role, ['admin', 'teller', 'staff'], true);
    }
}

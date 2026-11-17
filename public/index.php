<?php
session_start();

require_once __DIR__ . '/../vendor/autoload.php';

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$publicFile = __DIR__ . $uri;

if ($uri !== '/' && file_exists($publicFile) && is_file($publicFile)) {
    $ext = strtolower(pathinfo($publicFile, PATHINFO_EXTENSION));
    $mime = [
        'css' => 'text/css; charset=utf-8',
        'js' => 'application/javascript; charset=utf-8',
        'json' => 'application/json; charset=utf-8',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
        'txt' => 'text/plain; charset=utf-8',
    ];

    header('Content-Type: ' . ($mime[$ext] ?? 'application/octet-stream'));
    readfile($publicFile);
    exit;
}

require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/MemberModel.php';

use App\Auth;
use App\MemberModel;

$page = $_GET['page'] ?? 'login';

function view($path, $data = []){
    extract($data);
    $is_login = (basename($path) === 'login.php');
    require __DIR__ . '/../templates/' . $path;
}

if ($page === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST'){
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    if (Auth::attempt($email, $password)) {
        header('Location: ?page=dashboard'); exit;
    } else {
        $error = 'Invalid credentials';
        view('login.php', ['error'=>$error]);
    }
} elseif ($page === 'login') {
    $success = isset($_GET['reset']) && $_GET['reset'] === 'success' ? 'Password reset successful. Please sign in with your new password.' : null;
    view('login.php', ['success'=>$success]);
} elseif ($page === 'forgot_password') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = trim(strtolower((string)($_POST['email'] ?? '')));
        if ($email === '') {
            view('forgot_password.php', ['error' => 'Email is required.']);
            exit;
        }

        $code = Auth::createResetCode($email);
        if ($code === null) {
            view('forgot_password.php', ['error' => 'No account was found for that email address.']);
            exit;
        }

        $_SESSION['reset_email'] = $email;
        $_SESSION['reset_verified'] = false;

        view('verify_otp.php', [
            'email' => $email,
            'otp' => $code,
            'message' => 'A 6-digit verification code was generated for ' . $email . '. For this demo, use the code below.'
        ]);
        exit;
    }

    view('forgot_password.php');
} elseif ($page === 'verify_otp') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = trim(strtolower((string)($_POST['email'] ?? '')));
        $otp = trim((string)($_POST['otp'] ?? ''));

        if ($email === '' || $otp === '') {
            view('verify_otp.php', ['email' => $email, 'error' => 'Email and verification code are required.']);
            exit;
        }

        if (!Auth::verifyResetCode($email, $otp)) {
            view('verify_otp.php', ['email' => $email, 'error' => 'Invalid or expired verification code.']);
            exit;
        }

        $_SESSION['reset_email'] = $email;
        $_SESSION['reset_verified'] = true;
        header('Location: ?page=reset_password');
        exit;
    }

    $email = $_SESSION['reset_email'] ?? '';
    view('verify_otp.php', ['email' => $email]);
} elseif ($page === 'reset_password') {
    $email = $_SESSION['reset_email'] ?? '';
    if ($email === '' || empty($_SESSION['reset_verified'])) {
        header('Location: ?page=forgot_password');
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $newPassword = (string)($_POST['new_password'] ?? '');
        $confirmPassword = (string)($_POST['confirm_password'] ?? '');

        if ($newPassword === '' || $confirmPassword === '') {
            view('reset_password.php', ['email' => $email, 'error' => 'Both password fields are required.']);
            exit;
        }

        if (strlen($newPassword) < 8) {
            view('reset_password.php', ['email' => $email, 'error' => 'Password must be at least 8 characters long.']);
            exit;
        }

        if ($newPassword !== $confirmPassword) {
            view('reset_password.php', ['email' => $email, 'error' => 'Passwords do not match.']);
            exit;
        }

        if (!Auth::updatePassword($email, $newPassword)) {
            view('reset_password.php', ['email' => $email, 'error' => 'Password reset failed. Please try again.']);
            exit;
        }

        unset($_SESSION['reset_email'], $_SESSION['reset_verified']);
        header('Location: ?page=login&reset=success');
        exit;
    }

    view('reset_password.php', ['email' => $email]);
} elseif ($page === 'logout'){
    Auth::logout();
    header('Location: ?page=login'); exit;
} elseif ($page === 'dashboard'){
    if (!Auth::check()) { header('Location: ?page=login'); exit; }
    $model = new MemberModel();
    $dashboardData = [
        'user' => Auth::user(),
        'memberCount' => count($model->all()),
        'totalSavings' => $model->totalSavingsValue(),
        'loanBalance' => $model->totalLoanBalance(),
        'memberSavings' => $model->savingsSummary(),
        'memberLoans' => $model->loanSummary(),
    ];
    view('dashboard.php', $dashboardData);
} elseif ($page === 'members'){
    if (!Auth::check()) { header('Location: ?page=login'); exit; }
    $model = new MemberModel();
    $q = $_GET['q'] ?? null;
    $members = $q ? $model->search($q) : $model->all();
    view('members_list.php', ['members'=>$members, 'user'=>Auth::user(), 'q'=>$q]);
} elseif ($page === 'member_form'){
    if (!Auth::check()) { header('Location: ?page=login'); exit; }
    $model = new MemberModel();
    $id = $_GET['id'] ?? null;
    $member = $id ? $model->find($id) : null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST'){
        $data = $_POST;
        $dup = $model->findDuplicate($data['national_id'] ?? '', $data['phone'] ?? '', $data['email'] ?? '');
        if ($dup && !$id) {
            $warning = 'Possible duplicate found: ' . ($dup['first_name'] ?? '') . ' ' . ($dup['last_name'] ?? '');
            view('member_form.php', ['member'=>$data, 'warning'=>$warning, 'user'=>Auth::user()]);
            exit;
        }
        if ($id) {
            $model->update($id, $data);
        } else {
            $model->create($data);
        }
        header('Location: ?page=members'); exit;
    }
    view('member_form.php', ['member'=>$member, 'user'=>Auth::user()]);
} elseif ($page === 'member_delete'){
    if (!Auth::check()) { header('Location: ?page=login'); exit; }
    $id = $_GET['id'] ?? null;
    if ($id) { (new MemberModel())->delete($id); }
    header('Location: ?page=members'); exit;
} elseif ($page === 'export_csv'){
    if (!Auth::check()) { header('Location: ?page=login'); exit; }
    $members = (new MemberModel())->all();
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="members.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['id','first_name','last_name','national_id','phone','email','address','dob','joined_at']);
    foreach($members as $m) fputcsv($out, $m);
    fclose($out);
    exit;
} elseif ($page === 'users'){
    if (!Auth::check()) { header('Location: ?page=login'); exit; }
    $user = Auth::user();
    if (($user['role'] ?? '') !== 'admin') { header('HTTP/1.0 403 Forbidden'); echo 'Forbidden'; exit; }
    $pdo = \App\Database::getInstance();
    $stmt = $pdo->query('SELECT id,name,email,role,created_at FROM users ORDER BY id');
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    view('users_list.php', ['users'=>$users, 'user'=>$user]);
} elseif ($page === 'savings'){
    if (!Auth::check()) { header('Location: ?page=login'); exit; }
    $model = new MemberModel();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $memberId = $_POST['member_id'] ?? null;
        $amount = (float)($_POST['amount'] ?? 0);
        if ($memberId && $amount > 0) {
            $model->addSavings($memberId, $amount);
        }
        header('Location: ?page=savings'); exit;
    }
    view('savings.php', ['user' => Auth::user(), 'members' => $model->all(), 'savings' => $model->savingsSummary()]);
} elseif ($page === 'loans'){
    if (!Auth::check()) { header('Location: ?page=login'); exit; }
    $model = new MemberModel();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['loan_id'])) {
            $loanId = $_POST['loan_id'] ?? null;
            $status = $_POST['status'] ?? 'approved';
            if ($loanId) {
                $model->updateLoanStatus($loanId, $status);
            }
            header('Location: ?page=loans'); exit;
        }

        $memberId = $_POST['member_id'] ?? null;
        $amount = (float)($_POST['amount'] ?? 0);
        if ($memberId && $amount > 0) {
            $model->addLoan($memberId, $amount, $_POST['status'] ?? 'approved');
        }
        header('Location: ?page=loans'); exit;
    }
    view('loans.php', ['user' => Auth::user(), 'members' => $model->all(), 'loans' => $model->loanSummary()]);
} elseif ($page === 'member_detail'){
    if (!Auth::check()) { header('Location: ?page=login'); exit; }
    $model = new MemberModel();
    $id = $_GET['id'] ?? null;
    if (!$id) { header('Location: ?page=members'); exit; }
    $member = $model->find($id);
    if (!$member) { header('Location: ?page=members'); exit; }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'repay') {
        $loanId = $_POST['loan_id'] ?? null;
        $amount = (float)($_POST['amount'] ?? 0);
        if ($loanId) {
            $model->repayLoan($loanId, $amount);
        }
        header('Location: ?page=member_detail&id=' . (int)$id); exit;
    }

    view('member_detail.php', [
        'user' => Auth::user(),
        'member' => $member,
        'account' => $model->memberAccount($id),
        'loans' => $model->memberLoans($id),
        'payments' => $model->memberPaymentHistory($id)
    ]);
} elseif ($page === 'reports'){
    if (!Auth::check()) { header('Location: ?page=login'); exit; }
    $model = new MemberModel();
    $summaries = $model->savingsSummary();
    $loans = $model->loanSummary();
    $maxSavings = 0;
    foreach ($summaries as $row) {
        $maxSavings = max($maxSavings, (float)($row['savings_balance'] ?? 0));
    }
    view('reports.php', [
        'user' => Auth::user(),
        'memberCount' => count($model->all()),
        'totalSavings' => $model->totalSavingsValue(),
        'loanBalance' => $model->totalLoanBalance(),
        'memberSavings' => $summaries,
        'memberLoans' => $loans,
        'maxSavings' => $maxSavings,
    ]);
} elseif ($page === 'member_statement'){
    if (!Auth::check()) { header('Location: ?page=login'); exit; }
    $model = new MemberModel();
    $id = $_GET['id'] ?? null;
    if (!$id) { header('Location: ?page=members'); exit; }
    $member = $model->find($id);
    if (!$member) { header('Location: ?page=members'); exit; }
    view('member_statement.php', [
        'user' => Auth::user(),
        'member' => $member,
        'account' => $model->memberAccount($id),
        'loans' => $model->memberLoans($id),
        'payments' => $model->memberPaymentHistory($id),
    ]);
} elseif ($page === 'backup'){
    if (!Auth::check()) { header('Location: ?page=login'); exit; }
    $dbFile = __DIR__ . '/../data/database.sqlite';
    if (file_exists($dbFile)) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="memberlink_backup_' . date('Ymd_His') . '.sqlite"');
        readfile($dbFile);
        exit;
    }
    header('Location: ?page=dashboard'); exit;
} else {
    header('HTTP/1.0 404 Not Found');
    echo 'Page not found';
}

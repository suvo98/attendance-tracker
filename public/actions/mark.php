<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /');
    exit;
}

$action = (string)($_POST['action'] ?? '');

try {
    if ($action === 'login') {
        $userHash = trim((string)($_POST['user_hash'] ?? ''));
        if ($userHash === '') {
            header('Location: /?status=invalid_hash');
            exit;
        }

        $findUser = $pdo->prepare('SELECT id, name FROM users WHERE user_hash = :hash LIMIT 1');
        $findUser->execute([':hash' => $userHash]);
        $user = $findUser->fetch();

        if (!$user) {
            header('Location: /?status=invalid_hash');
            exit;
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['user_name'] = (string)$user['name'];

        header('Location: /?status=login_ok');
        exit;
    }

    if ($action === 'logout') {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        header('Location: /?status=logged_out');
        exit;
    }

    if (!isset($_SESSION['user_id'], $_SESSION['user_name'])) {
        header('Location: /?status=auth_required');
        exit;
    }

    $sessionUserId = (int)$_SESSION['user_id'];
    $sessionUserName = (string)$_SESSION['user_name'];

    if ($action === 'mark') {
        $remarks = trim((string)($_POST['remarks'] ?? ''));
        $remarksValue = $remarks === '' ? null : $remarks;
        $markedAt = (new DateTimeImmutable('now', new DateTimeZone('Asia/Dhaka')))->format('Y-m-d H:i:s');

        $insert = $pdo->prepare(
            'INSERT INTO attendance_logs (user_id, user_name, remarks, marked_at)
             VALUES (:user_id, :user_name, :remarks, :marked_at)'
        );
        $insert->execute([
            ':user_id' => $sessionUserId,
            ':user_name' => $sessionUserName,
            ':remarks' => $remarksValue,
            ':marked_at' => $markedAt,
        ]);

        header('Location: /?status=ok');
        exit;
    }

    if ($action === 'delete') {
        $logId = (int)($_POST['log_id'] ?? 0);
        if ($logId <= 0) {
            header('Location: /?status=invalid_delete');
            exit;
        }

        $deleteStmt = $pdo->prepare('DELETE FROM attendance_logs WHERE id = :id AND user_id = :user_id');
        $deleteStmt->execute([
            ':id' => $logId,
            ':user_id' => $sessionUserId,
        ]);

        if ($deleteStmt->rowCount() === 0) {
            header('Location: /?status=delete_denied');
            exit;
        }

        header('Location: /?status=deleted');
        exit;
    }

    header('Location: /?status=error');
    exit;
} catch (Throwable $e) {
    header('Location: /?status=error');
    exit;
}

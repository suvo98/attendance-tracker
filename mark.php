<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$userHash = trim((string)($_POST['user_hash'] ?? ''));

if ($userHash === '') {
    header('Location: index.php?status=invalid_hash');
    exit;
}

try {
    $findUser = $pdo->prepare('SELECT id FROM users WHERE user_hash = :hash LIMIT 1');
    $findUser->execute([':hash' => $userHash]);
    $user = $findUser->fetch();

    if (!$user) {
        header('Location: index.php?status=invalid_hash');
        exit;
    }

    $insert = $pdo->prepare('INSERT INTO attendance_logs (user_id, marked_at) VALUES (:user_id, NOW())');
    $insert->execute([':user_id' => (int)$user['id']]);

    header('Location: index.php?status=ok');
    exit;
} catch (Throwable $e) {
    header('Location: index.php?status=error');
    exit;
}

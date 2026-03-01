<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

$message = '';
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'login_ok') {
        $message = 'Login successful.';
    } elseif ($_GET['status'] === 'logged_out') {
        $message = 'Logged out successfully.';
    } elseif ($_GET['status'] === 'ok') {
        $message = 'Attendance saved successfully.';
    } elseif ($_GET['status'] === 'deleted') {
        $message = 'Your attendance log was deleted.';
    } elseif ($_GET['status'] === 'invalid_hash') {
        $message = 'Invalid user hash.';
    } elseif ($_GET['status'] === 'auth_required') {
        $message = 'Please login with hash first.';
    } elseif ($_GET['status'] === 'invalid_delete') {
        $message = 'Invalid log selected for delete.';
    } elseif ($_GET['status'] === 'delete_denied') {
        $message = 'You can delete only your own logs.';
    } elseif ($_GET['status'] === 'error') {
        $message = 'Something went wrong while saving attendance.';
    }
}

$isLoggedIn = isset($_SESSION['user_id'], $_SESSION['user_name']);
$currentUserId = $isLoggedIn ? (int)$_SESSION['user_id'] : 0;
$currentUserName = $isLoggedIn ? (string)$_SESSION['user_name'] : '';

$myLogs = [];
if ($isLoggedIn) {
    $myLogsStmt = $pdo->prepare(
        'SELECT id, user_name, remarks, marked_at
         FROM attendance_logs
         WHERE user_id = :user_id
         ORDER BY id DESC
         LIMIT 50'
    );
    $myLogsStmt->execute([':user_id' => $currentUserId]);
    $myLogs = $myLogsStmt->fetchAll();
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0f172a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <link rel="manifest" href="/manifest.json">
    <title>Attendance Tracker</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 30px auto; padding: 0 15px; }
        .card { border: 1px solid #ddd; border-radius: 8px; padding: 16px; margin-bottom: 16px; }
        .msg { margin-bottom: 16px; padding: 10px; border-radius: 6px; background: #f3f5f7; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f0f0f0; }
        button { padding: 8px 12px; cursor: pointer; }
        input, textarea { width: 100%; padding: 8px; margin: 8px 0; box-sizing: border-box; }
        .hash { font-family: Consolas, monospace; font-size: 12px; word-break: break-all; }
        .link-btn { display: inline-block; padding: 8px 12px; background: #f0f0f0; border: 1px solid #ddd; border-radius: 4px; text-decoration: none; color: #000; }
        .topbar { display: flex; justify-content: space-between; align-items: center; gap: 12px; }
        .inline { display: inline; }
        .muted { color: #555; font-size: 14px; margin-top: 0; }
    </style>
</head>
<body>
    <div class="topbar">
        <h1>Attendance Tracker</h1>
        <?php if ($isLoggedIn): ?>
            <form method="post" action="mark.php" class="inline">
                <input type="hidden" name="action" value="logout">
                <button type="submit">Logout</button>
            </form>
        <?php endif; ?>
    </div>

    <?php if ($message !== ''): ?>
        <div class="msg"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <?php if (!$isLoggedIn): ?>
        <div class="card">
            <h2>Login With Hash</h2>
            <p class="muted">Login once with hash. Session will keep you signed in.</p>
            <form method="post" action="mark.php">
                <input type="hidden" name="action" value="login">
                <label for="user_hash">User Hash:</label>
                <input type="text" id="user_hash" name="user_hash" required autocomplete="off">
                <button type="submit">Login</button>
            </form>
        </div>
    <?php else: ?>
        <div class="card">
            <h2>Mark Attendance</h2>
            <p class="muted">Logged in as: <strong><?= htmlspecialchars($currentUserName, ENT_QUOTES, 'UTF-8') ?></strong></p>
            <form method="post" action="mark.php">
                <input type="hidden" name="action" value="mark">
                <label for="remarks">Remarks (optional):</label>
                <textarea id="remarks" name="remarks" rows="3" placeholder="Write optional remarks"></textarea>
                <button type="submit">Save Attendance</button>
            </form>
        </div>
    <?php endif; ?>

    <div class="card">
        <h2>Registered Users</h2>
        <a class="link-btn" href="users.php">Go To Users Route</a>
    </div>

    <?php if ($isLoggedIn): ?>
        <div class="card">
            <h2>My Attendance Logs</h2>
            <table>
                <thead>
                    <tr>
                        <th>Log ID</th>
                        <th>User</th>
                        <th>Remarks</th>
                        <th>DateTime</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($myLogs) === 0): ?>
                        <tr><td colspan="5">No attendance yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($myLogs as $row): ?>
                            <tr>
                                <td><?= (int)$row['id'] ?></td>
                                <td><?= htmlspecialchars($row['user_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)($row['remarks'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($row['marked_at'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <form method="post" action="mark.php" class="inline" onsubmit="return confirm('Delete this log?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="log_id" value="<?= (int)$row['id'] ?>">
                                        <button type="submit">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="card">
            <h2>My Attendance Logs</h2>
            <p class="muted">Login with hash first to submit and view logs.</p>
        </div>
    <?php endif; ?>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register('/service-worker.js');
            });
        }
    </script>
</body>
</html>

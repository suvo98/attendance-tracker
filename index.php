<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

$message = '';
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'ok') {
        $message = 'Attendance saved successfully.';
    } elseif ($_GET['status'] === 'invalid_hash') {
        $message = 'Invalid user hash.';
    } elseif ($_GET['status'] === 'error') {
        $message = 'Something went wrong while saving attendance.';
    }
}

$recentLogsStmt = $pdo->query(
    'SELECT a.id, u.name, u.user_hash, a.marked_at
     FROM attendance_logs a
     INNER JOIN users u ON u.id = a.user_id
     ORDER BY a.id DESC
     LIMIT 20'
);
$recentLogs = $recentLogsStmt->fetchAll();
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
        .hash { font-family: Consolas, monospace; font-size: 12px; word-break: break-all; }
        .link-btn { display: inline-block; padding: 8px 12px; background: #f0f0f0; border: 1px solid #ddd; border-radius: 4px; text-decoration: none; color: #000; }
    </style>
</head>
<body>
    <h1>Attendance Tracker</h1>

    <?php if ($message !== ''): ?>
        <div class="msg"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <div class="card">
        <h2>Mark Attendance</h2>
        <form method="post" action="mark.php">
            <label for="user_hash">User Hash:</label><br>
            <input type="text" id="user_hash" name="user_hash" required style="width:100%;padding:8px;margin:8px 0;">
            <button type="submit">Submit Attendance</button>
        </form>
    </div>

    <div class="card">
        <h2>Registered Users</h2>
        <a class="link-btn" href="users.php">Go To Users Route</a>
    </div>

    <div class="card">
        <h2>Recent Attendance Logs</h2>
        <table>
            <thead>
                <tr>
                    <th>Log ID</th>
                    <th>User</th>
                    <th>User Hash</th>
                    <th>Marked At</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($recentLogs) === 0): ?>
                    <tr><td colspan="4">No attendance yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($recentLogs as $row): ?>
                        <tr>
                            <td><?= (int)$row['id'] ?></td>
                            <td><?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="hash"><?= htmlspecialchars($row['user_hash'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($row['marked_at'], ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register('/service-worker.js');
            });
        }
    </script>
</body>
</html>

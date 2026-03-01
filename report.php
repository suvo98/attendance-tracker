<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

if (!isset($_SESSION['user_id'], $_SESSION['user_name'])) {
    header('Location: index.php?status=auth_required');
    exit;
}

$currentUserId = (int)$_SESSION['user_id'];
$currentUserName = (string)$_SESSION['user_name'];

$today = (new DateTimeImmutable('now', new DateTimeZone('Asia/Dhaka')))->format('Y-m-d');
$startDate = (string)($_GET['start_date'] ?? $today);
$endDate = (string)($_GET['end_date'] ?? $today);
$error = '';
$rows = [];

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
    $error = 'Invalid date format.';
} elseif ($startDate > $endDate) {
    $error = 'Start date cannot be after end date.';
} else {
    $startDateTime = $startDate . ' 00:00:00';
    $endDateTime = $endDate . ' 23:59:59';

    $stmt = $pdo->prepare(
        'SELECT id, user_name, remarks, marked_at
         FROM attendance_logs
         WHERE user_id = :user_id
           AND marked_at BETWEEN :start_dt AND :end_dt
         ORDER BY marked_at ASC, id ASC'
    );
    $stmt->execute([
        ':user_id' => $currentUserId,
        ':start_dt' => $startDateTime,
        ':end_dt' => $endDateTime,
    ]);
    $rows = $stmt->fetchAll();
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
    <title>Date Range Report</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 30px auto; padding: 0 15px; }
        .card { border: 1px solid #ddd; border-radius: 8px; padding: 16px; margin-bottom: 16px; }
        .msg { margin-bottom: 16px; padding: 10px; border-radius: 6px; background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .actions { display: flex; gap: 8px; flex-wrap: wrap; }
        .actions button, .actions a { padding: 8px 12px; cursor: pointer; border: 1px solid #ddd; border-radius: 4px; background: #f8fafc; text-decoration: none; color: #000; }
        .filters { display: grid; grid-template-columns: 1fr 1fr auto; gap: 10px; align-items: end; }
        .filters label { display: block; font-size: 14px; margin-bottom: 4px; }
        .filters input { width: 100%; padding: 8px; box-sizing: border-box; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; vertical-align: top; }
        th { background: #f0f0f0; }
        .meta { color: #333; margin: 10px 0; }
        .no-print { display: block; }
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; max-width: 100%; padding: 0; }
            .card { border: none; padding: 0; margin: 0 0 12px; }
            a { text-decoration: none; color: #000; }
        }
    </style>
</head>
<body>
    <div class="card no-print">
        <h1>Date Range Report</h1>
        <p>User: <strong><?= htmlspecialchars($currentUserName, ENT_QUOTES, 'UTF-8') ?></strong></p>
        <form method="get" action="report.php" class="filters">
            <div>
                <label for="start_date">Start Date</label>
                <input type="date" id="start_date" name="start_date" value="<?= htmlspecialchars($startDate, ENT_QUOTES, 'UTF-8') ?>" required>
            </div>
            <div>
                <label for="end_date">End Date</label>
                <input type="date" id="end_date" name="end_date" value="<?= htmlspecialchars($endDate, ENT_QUOTES, 'UTF-8') ?>" required>
            </div>
            <div class="actions">
                <button type="submit">Search</button>
            </div>
        </form>
        <div class="actions" style="margin-top: 10px;">
            <button type="button" onclick="window.print()">Print as PDF</button>
            <a href="index.php">Back To Home</a>
        </div>
    </div>

    <?php if ($error !== ''): ?>
        <div class="msg no-print"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php else: ?>
        <div class="card">
            <h2>Attendance Report</h2>
            <p class="meta">
                User: <strong><?= htmlspecialchars($currentUserName, ENT_QUOTES, 'UTF-8') ?></strong> |
                Range: <strong><?= htmlspecialchars($startDate, ENT_QUOTES, 'UTF-8') ?></strong> to <strong><?= htmlspecialchars($endDate, ENT_QUOTES, 'UTF-8') ?></strong> |
                Total: <strong><?= count($rows) ?></strong>
            </p>
            <table>
                <thead>
                    <tr>
                        <th>Log ID</th>
                        <th>User</th>
                        <th>Remarks</th>
                        <th>DateTime</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($rows) === 0): ?>
                        <tr><td colspan="4">No data found in selected date range.</td></tr>
                    <?php else: ?>
                        <?php foreach ($rows as $row): ?>
                            <tr>
                                <td><?= (int)$row['id'] ?></td>
                                <td><?= htmlspecialchars($row['user_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)($row['remarks'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($row['marked_at'], ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
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

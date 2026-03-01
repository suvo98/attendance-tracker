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
    <meta name="theme-color" content="#0ea5a4">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <link rel="manifest" href="/manifest.json">
    <link rel="stylesheet" href="/styles.css">
    <title>Date Range Report</title>
</head>
<body>
    <main class="shell">
        <header class="hero no-print">
            <div>
                <h1 class="title">Date Range Report</h1>
                <p class="subtitle">Filter attendance logs and export PDF instantly.</p>
            </div>
            <div class="actions">
                <a class="btn-link ghost" href="index.php">Back To Home</a>
                <button class="btn primary" type="button" onclick="window.print()">Print as PDF</button>
            </div>
        </header>

        <?php if ($error !== ''): ?>
            <div class="msg error no-print"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <section class="panel no-print">
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
                    <button class="btn primary" type="submit">Search</button>
                </div>
            </form>
        </section>

        <?php if ($error === ''): ?>
            <section class="panel">
                <div class="kpi" style="margin-bottom: 12px;">
                    <div class="kpi-item"><span class="meta">User</span><strong><?= htmlspecialchars($currentUserName, ENT_QUOTES, 'UTF-8') ?></strong></div>
                    <div class="kpi-item"><span class="meta">Range</span><strong><?= htmlspecialchars($startDate, ENT_QUOTES, 'UTF-8') ?> to <?= htmlspecialchars($endDate, ENT_QUOTES, 'UTF-8') ?></strong></div>
                    <div class="kpi-item"><span class="meta">Total Rows</span><strong><?= count($rows) ?></strong></div>
                </div>

                <div class="table-wrap">
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
            </section>
        <?php endif; ?>
    </main>

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register('/service-worker.js');
            });
        }
    </script>
</body>
</html>
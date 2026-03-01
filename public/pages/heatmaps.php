<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';

$tz = new DateTimeZone('Asia/Dhaka');
$today = new DateTimeImmutable('now', $tz);
$month = (string)($_GET['month'] ?? $today->format('Y-m'));

if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
    $month = $today->format('Y-m');
}

$monthStart = new DateTimeImmutable($month . '-01', $tz);
$monthEnd = $monthStart->modify('last day of this month');

$startDate = $monthStart->format('Y-m-d');
$endDate = $monthEnd->format('Y-m-d');
$startDateTime = $startDate . ' 00:00:00';
$endDateTime = $endDate . ' 23:59:59';

$days = [];
$cursor = $monthStart;
while ($cursor <= $monthEnd) {
    $days[] = $cursor->format('Y-m-d');
    $cursor = $cursor->modify('+1 day');
}

$users = $pdo->query('SELECT id, name FROM users ORDER BY id ASC')->fetchAll();

$counts = [];
$aggStmt = $pdo->prepare(
    'SELECT user_id, DATE(marked_at) AS log_date, COUNT(*) AS total
     FROM attendance_logs
     WHERE marked_at BETWEEN :start_dt AND :end_dt
     GROUP BY user_id, DATE(marked_at)'
);
$aggStmt->execute([
    ':start_dt' => $startDateTime,
    ':end_dt' => $endDateTime,
]);

$maxCount = 0;
foreach ($aggStmt->fetchAll() as $row) {
    $uid = (int)$row['user_id'];
    $date = (string)$row['log_date'];
    $total = (int)$row['total'];
    $counts[$uid][$date] = $total;
    if ($total > $maxCount) {
        $maxCount = $total;
    }
}

function heatCellStyle(int $value, int $max): string
{
    if ($value <= 0 || $max <= 0) {
        return 'background:#f8fafc;color:#9aa5b1;';
    }

    $ratio = $value / $max;
    $alpha = 0.25 + (0.75 * $ratio);
    $alphaText = number_format($alpha, 2, '.', '');

    return "background:rgba(14,165,164,{$alphaText});color:#052e2b;";
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0ea5a4">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <link rel="manifest" href="/assets/manifest.json">
    <link rel="stylesheet" href="/assets/styles.css">
    <title>User Heatmaps</title>
    <style>
        .heatmap-table {
            min-width: 980px;
        }
        .heatmap-table th,
        .heatmap-table td {
            text-align: center;
            white-space: nowrap;
        }
        .heatmap-table th:first-child,
        .heatmap-table td:first-child {
            position: sticky;
            left: 0;
            z-index: 1;
            text-align: left;
            background: #f7fbff;
            min-width: 160px;
        }
        .heat-cell {
            font-weight: 600;
            border-radius: 8px;
        }
        .legend {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            color: var(--muted);
            font-size: 0.9rem;
        }
        .legend-box {
            width: 22px;
            height: 14px;
            border-radius: 4px;
            border: 1px solid #d7e3f0;
        }
    </style>
</head>
<body>
    <main class="shell">
        <header class="hero">
            <div>
                <h1 class="title">Attendance Heatmaps</h1>
                <p class="subtitle">User-wise monthly attendance intensity map.</p>
            </div>
            <div class="actions no-print">
                <a class="btn-link ghost" href="/">Back To Home</a>
            </div>
        </header>

        <section class="panel no-print">
            <form method="get" action="/pages/heatmaps.php" class="filters">
                <div>
                    <label for="month">Month</label>
                    <input type="month" id="month" name="month" value="<?= htmlspecialchars($month, ENT_QUOTES, 'UTF-8') ?>" required>
                </div>
                <div class="actions">
                    <button class="btn primary" type="submit">Load Heatmap</button>
                </div>
            </form>
        </section>

        <section class="panel">
            <div class="kpi" style="margin-bottom: 12px;">
                <div class="kpi-item"><span class="meta">Users</span><strong><?= count($users) ?></strong></div>
                <div class="kpi-item"><span class="meta">Month</span><strong><?= htmlspecialchars($monthStart->format('F Y'), ENT_QUOTES, 'UTF-8') ?></strong></div>
                <div class="kpi-item"><span class="meta">Peak Entries/Day</span><strong><?= $maxCount ?></strong></div>
            </div>

            <div class="legend" style="margin-bottom: 10px;">
                <span>Less</span>
                <span class="legend-box" style="background:#f8fafc;"></span>
                <span class="legend-box" style="background:rgba(14,165,164,0.35);"></span>
                <span class="legend-box" style="background:rgba(14,165,164,0.65);"></span>
                <span class="legend-box" style="background:rgba(14,165,164,1);"></span>
                <span>More</span>
            </div>

            <div class="table-wrap">
                <table class="heatmap-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <?php foreach ($days as $day): ?>
                                <th><?= (new DateTimeImmutable($day))->format('d') ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($users) === 0): ?>
                            <tr>
                                <td colspan="<?= count($days) + 1 ?>">No users found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                                <?php $uid = (int)$user['id']; ?>
                                <tr>
                                    <td><?= htmlspecialchars((string)$user['name'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <?php foreach ($days as $day): ?>
                                        <?php $value = (int)($counts[$uid][$day] ?? 0); ?>
                                        <td class="heat-cell" style="<?= htmlspecialchars(heatCellStyle($value, $maxCount), ENT_QUOTES, 'UTF-8') ?>">
                                            <?= $value > 0 ? $value : '' ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register('/assets/service-worker.js');
            });
        }
    </script>
</body>
</html>

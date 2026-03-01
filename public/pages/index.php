<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';

$message = '';
$messageClass = 'msg';
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
        $messageClass = 'msg error';
    } elseif ($_GET['status'] === 'auth_required') {
        $message = 'Please login with hash first.';
        $messageClass = 'msg error';
    } elseif ($_GET['status'] === 'invalid_delete') {
        $message = 'Invalid log selected for delete.';
        $messageClass = 'msg error';
    } elseif ($_GET['status'] === 'delete_denied') {
        $message = 'You can delete only your own logs.';
        $messageClass = 'msg error';
    } elseif ($_GET['status'] === 'error') {
        $message = 'Something went wrong while saving attendance.';
        $messageClass = 'msg error';
    }
}

$isLoggedIn = isset($_SESSION['user_id'], $_SESSION['user_name']);
$currentUserId = $isLoggedIn ? (int)$_SESSION['user_id'] : 0;
$currentUserName = $isLoggedIn ? (string)$_SESSION['user_name'] : '';
$currentUserInitial = '';
if ($isLoggedIn) {
    $trimmedName = trim($currentUserName);
    $currentUserInitial = $trimmedName !== '' ? strtoupper(substr($trimmedName, 0, 1)) : '?';
}

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
    <meta name="theme-color" content="#0ea5a4">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <link rel="manifest" href="/assets/manifest.json">
    <link rel="stylesheet" href="/assets/styles.css">
    <title>Attendance Tracker</title>
</head>
<body class="has-dock">
    <main class="shell">
        <header class="hero">
            <div>
                <h1 class="title">Attendance Tracker</h1>
                <p class="subtitle">Fast hash login, one-tap attendance, and personal report export.</p>
            </div>
            <div class="actions no-print">
                <span class="pill">Timezone: Asia/Dhaka (GMT+6)</span>
                <?php if ($isLoggedIn): ?>
                    <span class="user-avatar" title="<?= htmlspecialchars($currentUserName, ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars($currentUserInitial, ENT_QUOTES, 'UTF-8') ?>
                    </span>
                    <form method="post" action="/actions/mark.php">
                        <input type="hidden" name="action" value="logout">
                        <button class="btn ghost" type="submit">Logout</button>
                    </form>
                <?php endif; ?>
            </div>
        </header>

        <?php if ($message !== ''): ?>
            <div class="<?= htmlspecialchars($messageClass, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <section class="grid-2">
            <?php if (!$isLoggedIn): ?>
                <article class="panel">
                    <h2>Login With User Hash</h2>
                    <p class="meta">Login once. Session keeps you signed in for long-term use.</p>
                    <form method="post" action="/actions/mark.php">
                        <input type="hidden" name="action" value="login">
                        <label for="user_hash">User Hash</label>
                        <input type="text" id="user_hash" name="user_hash" required autocomplete="off" placeholder="Paste your unique hash">
                        <div class="actions" style="margin-top: 10px;">
                            <button class="btn primary" type="submit">Login</button>
                        </div>
                    </form>
                </article>
            <?php else: ?>
                <article class="panel">
                    <h2>Mark Attendance</h2>
                    <p class="meta">Logged in as <strong><?= htmlspecialchars($currentUserName, ENT_QUOTES, 'UTF-8') ?></strong></p>
                    <form method="post" action="/actions/mark.php">
                        <input type="hidden" name="action" value="mark">
                        <label for="remarks">Remarks (optional)</label>
                        <textarea id="remarks" name="remarks" placeholder="Add optional note for this entry"></textarea>
                        <div class="actions" style="margin-top: 10px;">
                            <button class="btn primary" type="submit">Save Attendance</button>
                        </div>
                    </form>
                </article>
            <?php endif; ?>

            <article class="panel">
                <h2>Quick Access</h2>
                <p class="meta">Navigate to users, reports, and heatmap visualization tools.</p>
                <div class="actions" style="margin-top: 12px;">
                    <a class="btn-link ghost" href="/pages/users.php">Registered Users</a>
                    <a class="btn-link ghost" href="/pages/heatmaps.php">Heatmaps</a>
                    <?php if ($isLoggedIn): ?>
                        <a class="btn-link primary" href="/pages/report.php">Date Range Report</a>
                    <?php endif; ?>
                </div>
            </article>
        </section>

        <?php if ($isLoggedIn): ?>
            <article class="panel" style="margin-top: 14px;">
                <h2>My Attendance Logs</h2>
                <div class="kpi" style="margin: 10px 0 14px;">
                    <div class="kpi-item"><span class="meta">Total Rows</span><strong><?= count($myLogs) ?></strong></div>
                    <div class="kpi-item"><span class="meta">User</span><strong><?= htmlspecialchars($currentUserName, ENT_QUOTES, 'UTF-8') ?></strong></div>
                    <div class="kpi-item"><span class="meta">Latest ID</span><strong><?= count($myLogs) > 0 ? (int)$myLogs[0]['id'] : 0 ?></strong></div>
                </div>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Log ID</th>
                                <th>User</th>
                                <th>Remarks</th>
                                <th>DateTime</th>
                                <th class="no-print">Action</th>
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
                                        <td class="no-print">
                                            <form method="post" action="/actions/mark.php" onsubmit="return confirm('Delete this log?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="log_id" value="<?= (int)$row['id'] ?>">
                                                <button class="btn danger" type="submit">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </article>
        <?php else: ?>
            <article class="panel" style="margin-top: 14px;">
                <h2>My Attendance Logs</h2>
                <p class="meta">Login with your hash to submit entries and view logs.</p>
            </article>
        <?php endif; ?>
    </main>
    <nav class="dock no-print" data-dock aria-label="Quick navigation">
        <a class="dock-item" href="/pages/users.php" aria-label="Users">
            <span class="dock-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none">
                    <path d="M16 20v-1.1c0-1.6-1.4-2.9-3-2.9H7c-1.6 0-3 1.3-3 2.9V20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    <circle cx="10" cy="8" r="3" stroke="currentColor" stroke-width="1.8"/>
                    <path d="M20 20v-1.1c0-1.1-.7-2-1.7-2.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    <path d="M15.5 5.4a3 3 0 0 1 0 5.2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
            </span>
            <span class="dock-label">Users</span>
        </a>
        <a class="dock-item" href="/pages/heatmaps.php" aria-label="Heatmaps">
            <span class="dock-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none">
                    <rect x="3" y="3" width="6" height="6" rx="1.4" stroke="currentColor" stroke-width="1.8"/>
                    <rect x="10.5" y="3" width="4.5" height="6" rx="1.2" stroke="currentColor" stroke-width="1.8"/>
                    <rect x="16.5" y="3" width="4.5" height="6" rx="1.2" stroke="currentColor" stroke-width="1.8"/>
                    <rect x="3" y="10.5" width="4.5" height="4.5" rx="1.2" stroke="currentColor" stroke-width="1.8"/>
                    <rect x="8.8" y="10.5" width="6.2" height="4.5" rx="1.2" stroke="currentColor" stroke-width="1.8"/>
                    <rect x="16.5" y="10.5" width="4.5" height="4.5" rx="1.2" stroke="currentColor" stroke-width="1.8"/>
                    <rect x="3" y="16.2" width="4.5" height="4.8" rx="1.2" stroke="currentColor" stroke-width="1.8"/>
                    <rect x="8.8" y="16.2" width="12.2" height="4.8" rx="1.2" stroke="currentColor" stroke-width="1.8"/>
                </svg>
            </span>
            <span class="dock-label">Heatmaps</span>
        </a>
        <a class="dock-item" href="/pages/report.php" aria-label="Report">
            <span class="dock-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none">
                    <path d="M7 3h7l4 4v14H7z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                    <path d="M14 3v4h4" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                    <path d="M10 12h5M10 16h5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
            </span>
            <span class="dock-label">Report</span>
        </a>
    </nav>

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register('/service-worker.js');
            });
        }

        (function () {
            const dock = document.querySelector('[data-dock]');
            if (!dock) {
                return;
            }

            const items = Array.from(dock.querySelectorAll('.dock-item'));
            const radius = 140;
            const maxScale = 1.55;

            const reset = () => {
                items.forEach((item) => item.style.setProperty('--scale', '1'));
            };

            dock.addEventListener('mousemove', (event) => {
                const mouseX = event.clientX;

                items.forEach((item) => {
                    const rect = item.getBoundingClientRect();
                    const centerX = rect.left + rect.width / 2;
                    const distance = Math.abs(mouseX - centerX);

                    let scale = 1;
                    if (distance < radius) {
                        const power = 1 - distance / radius;
                        scale = 1 + (maxScale - 1) * power;
                    }

                    item.style.setProperty('--scale', scale.toFixed(3));
                });
            });

            dock.addEventListener('mouseleave', reset);
            dock.addEventListener('touchstart', reset, { passive: true });
            reset();
        })();
    </script>
</body>
</html>

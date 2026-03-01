<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

$users = $pdo->query('SELECT id, name, user_hash FROM users ORDER BY id ASC')->fetchAll();
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
    <title>Registered Users</title>
</head>
<body>
    <main class="shell">
        <header class="hero">
            <div>
                <h1 class="title">Registered Users</h1>
                <p class="subtitle">Assigned names and unique hash keys.</p>
            </div>
            <div class="actions no-print">
                <a class="btn-link ghost" href="index.php">Back To Home</a>
            </div>
        </header>

        <section class="panel">
            <div class="kpi" style="margin-bottom: 12px;">
                <div class="kpi-item"><span class="meta">Total Users</span><strong><?= count($users) ?></strong></div>
                <div class="kpi-item"><span class="meta">Hash Length</span><strong>64</strong></div>
                <div class="kpi-item"><span class="meta">Status</span><strong>Active</strong></div>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Hash</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= (int)$user['id'] ?></td>
                                <td><?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="hash"><?= htmlspecialchars($user['user_hash'], ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
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
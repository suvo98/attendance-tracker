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
    <meta name="theme-color" content="#0f172a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <link rel="manifest" href="/manifest.json">
    <title>Registered Users</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 30px auto; padding: 0 15px; }
        .card { border: 1px solid #ddd; border-radius: 8px; padding: 16px; margin-bottom: 16px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f0f0f0; }
        .hash { font-family: Consolas, monospace; font-size: 12px; word-break: break-all; }
        .link-btn { display: inline-block; padding: 8px 12px; background: #f0f0f0; border: 1px solid #ddd; border-radius: 4px; text-decoration: none; color: #000; }
    </style>
</head>
<body>
    <h1>Registered Users (4)</h1>

    <div class="card">
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

    <a class="link-btn" href="index.php">Back To Home</a>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register('/service-worker.js');
            });
        }
    </script>
</body>
</html>

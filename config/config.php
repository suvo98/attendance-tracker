<?php

declare(strict_types=1);

function loadEnvFile(string $envPath): void
{
    if (!is_file($envPath) || !is_readable($envPath)) {
        return;
    }

    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '' || startsWith($trimmed, '#')) {
            continue;
        }

        $parts = explode('=', $trimmed, 2);
        if (count($parts) !== 2) {
            continue;
        }

        $key = trim($parts[0]);
        $value = trim($parts[1]);
        if ($key === '') {
            continue;
        }

        if (
            (startsWith($value, '"') && endsWith($value, '"')) ||
            (startsWith($value, "'") && endsWith($value, "'"))
        ) {
            $value = substr($value, 1, -1);
        }

        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}

function startsWith(string $haystack, string $needle): bool
{
    if ($needle === '') {
        return true;
    }
    return substr($haystack, 0, strlen($needle)) === $needle;
}

function endsWith(string $haystack, string $needle): bool
{
    if ($needle === '') {
        return true;
    }
    $length = strlen($needle);
    if ($length > strlen($haystack)) {
        return false;
    }
    return substr($haystack, -$length) === $needle;
}

function envValue(string $key, string $default): string
{
    $value = getenv($key);
    if ($value === false || $value === '') {
        return $default;
    }
    return $value;
}

$rootPath = dirname(__DIR__);
loadEnvFile($rootPath . '/.env');

$appTimezone = envValue('APP_TIMEZONE', 'Asia/Dhaka');
date_default_timezone_set($appTimezone);

// 6 months = 60 * 60 * 24 * 30 * 6 seconds
$sessionLifetime = (int)envValue('SESSION_LIFETIME', '15552000');
ini_set('session.gc_maxlifetime', (string)$sessionLifetime);
session_set_cookie_params([
    'lifetime' => $sessionLifetime,
    'path' => '/',
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax',
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = envValue('DB_HOST', '127.0.0.1');
$port = (int)envValue('DB_PORT', '3307');
$dbName = envValue('DB_NAME', 'attendance_tracker');
$dbUser = envValue('DB_USER', 'root');
$dbPass = envValue('DB_PASS', '');
$charset = envValue('DB_CHARSET', 'utf8mb4');

$dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset={$charset}";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
    $pdo->exec("SET time_zone = '+06:00'");
} catch (PDOException $e) {
    http_response_code(500);
    exit('Database connection failed: ' . $e->getMessage());
}

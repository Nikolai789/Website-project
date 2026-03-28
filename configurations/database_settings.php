<?php

$projectRoot = dirname(__DIR__);
$autoloadPath = $projectRoot . '/vendor/autoload.php';

if (is_file($autoloadPath)) {
    require_once $autoloadPath;
}

if (class_exists(\Dotenv\Dotenv::class)) {
    \Dotenv\Dotenv::createImmutable($projectRoot)->safeLoad();
}

function envValue(string $key, string $default): string
{
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

    if ($value === false || $value === null || $value === '') {
        return $default;
    }

    return (string) $value;
}

if (!defined('DB_HOST')) {
    define('DB_HOST', envValue('DB_HOST', 'localhost'));
}

if (!defined('DB_USER')) {
    define('DB_USER', envValue('DB_USER', 'root'));
}

if (!defined('DB_PASSWORD')) {
    define('DB_PASSWORD', envValue('DB_PASSWORD', ''));
}

if (!defined('DB_NAME')) {
    define('DB_NAME', envValue('DB_NAME', 'gearhubDB'));
}

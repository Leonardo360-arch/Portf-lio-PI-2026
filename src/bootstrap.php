<?php

declare(strict_types=1);

$autoloadPath = __DIR__ . '/../vendor/autoload.php';
$helpersPath = __DIR__ . '/helpers.php';

if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
} elseif (file_exists($helpersPath)) {
    require_once $helpersPath;
}

if (class_exists(Dotenv\Dotenv::class)) {
    Dotenv\Dotenv::createImmutable(dirname(__DIR__))->safeLoad();
}

date_default_timezone_set(env('APP_TIMEZONE', 'America/Sao_Paulo'));

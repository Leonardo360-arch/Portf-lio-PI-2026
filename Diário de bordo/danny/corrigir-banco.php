<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/src/bootstrap.php';
require_once __DIR__ . '/src/SchemaFixer.php';

use Danny\Database;
use Danny\SchemaFixer;

if (!isset($_SESSION['admin_id'])) {
  header('Location: login.php');
  exit;
}

try {
  $pdo = Database::connection();
  SchemaFixer::run($pdo);

  echo '<h1>Correção aplicada</h1>';
  echo '<p>As colunas, status e triggers foram verificadas/corrigidas.</p>';
  echo '<p><a href="admin.php">Voltar para o painel</a></p>';
} catch (Throwable $e) {
  echo '<h1>Erro ao aplicar correção</h1>';
  echo '<p>' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
  echo '<p><a href="admin.php">Voltar para o painel</a></p>';
}

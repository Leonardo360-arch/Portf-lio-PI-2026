<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

use Danny\Database;

session_start();

if (empty($_SESSION['admin_autorizado'])) {
    header('Location: ../login.php');
    exit;
}

$tipo = $_POST['tipo'] ?? '';
$id = (int) ($_POST['id'] ?? 0);

$tabelas = [
    'servico' => 'tipos_servico',
    'cenario' => 'cenarios',
    'fotografo' => 'fotografos',];

if ($id <= 0 || !isset($tabelas[$tipo])) {
    header('Location: ../servicos-cadastrados.php?erro=status');
    exit;
}

try {
    $pdo = Database::connection();

    $stmt = $pdo->prepare("DELETE FROM {$tabelas[$tipo]} WHERE id = :id");
    $stmt->execute([':id' => $id]);

    header('Location: ../servicos-cadastrados.php?opcao=excluida');
    exit;
} catch (PDOException $e) {
    header('Location: ../servicos-cadastrados.php?erro=banco');
    exit;
}

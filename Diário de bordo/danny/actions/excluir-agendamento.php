<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

use Danny\Database;

session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin.php');
    exit;
}

$agendamentoId = (int) ($_POST['agendamento_id'] ?? 0);

if ($agendamentoId <= 0) {
    header('Location: ../admin.php?erro=status#agenda');
    exit;
}

try {
    $pdo = Database::connection();

    $stmt = $pdo->prepare("DELETE FROM agendamentos WHERE id = :id");
    $stmt->execute([':id' => $agendamentoId]);

    header('Location: ../admin.php?agendamento=excluido#agenda');
    exit;
} catch (PDOException $e) {
    header('Location: ../admin.php?erro=banco#agenda');
    exit;
}

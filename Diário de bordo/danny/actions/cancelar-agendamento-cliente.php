<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

use Danny\Database;

session_start();

if (!isset($_SESSION['cliente_id'])) {
    header('Location: ../login-cliente.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../cliente.php#meus-agendamentos');
    exit;
}

$clienteId = (int) $_SESSION['cliente_id'];
$agendamentoId = (int) ($_POST['agendamento_id'] ?? 0);

if ($agendamentoId <= 0) {
    header('Location: ../cliente.php?erro=cancelamento#meus-agendamentos');
    exit;
}

try {
    $pdo = Database::connection();

    $stmt = $pdo->prepare("
        UPDATE agendamentos
        SET status = 'CANCELADO'
        WHERE id = :id
          AND admin_id = :cliente_id
          AND status = 'PENDENTE'
    ");

    $stmt->execute([
        ':id' => $agendamentoId,
        ':cliente_id' => $clienteId,
    ]);

    if ($stmt->rowCount() === 0) {
        header('Location: ../cliente.php?erro=cancelamento#meus-agendamentos');
        exit;
    }

    header('Location: ../cliente.php?cancelamento=sucesso#meus-agendamentos');
    exit;
} catch (PDOException $e) {
    header('Location: ../cliente.php?erro=cancelamento#meus-agendamentos');
    exit;
}

<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/AgendamentoHelper.php';

use Danny\Database;
use function Danny\validarAgendamento;
use function Danny\erroBancoParaCodigo;

session_start();

if (empty($_SESSION['admin_autorizado'])) {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin.php');
    exit;
}

$agendamentoId = (int) ($_POST['agendamento_id'] ?? 0);
$status = $_POST['status'] ?? '';

if ($agendamentoId <= 0 || !in_array($status, ['PENDENTE', 'CONFIRMADO', 'RECUSADO', 'CANCELADO', 'CONCLUIDO'], true)) {
    header('Location: ../admin.php?erro=status#agenda');
    exit;
}

try {
    $pdo = Database::connection();

    $stmtAgendamento = $pdo->prepare("
        SELECT id, fotografo_id, tipo_servico_id, cenario_id, data, hora_inicio
        FROM agendamentos
        WHERE id = :id
        LIMIT 1
    ");
    $stmtAgendamento->execute([':id' => $agendamentoId]);
    $agendamento = $stmtAgendamento->fetch();

    if (!$agendamento) {
        header('Location: ../admin.php?erro=status#agenda');
        exit;
    }

    if (in_array($status, ['PENDENTE', 'CONFIRMADO'], true)) {
        [$valido, $motivo] = validarAgendamento(
            $pdo,
            (int) $agendamento['fotografo_id'],
            (int) $agendamento['tipo_servico_id'],
            $agendamento['cenario_id'] !== null ? (int) $agendamento['cenario_id'] : null,
            $agendamento['data'],
            $agendamento['hora_inicio'],
            $status,
            $agendamentoId
        );

        if (!$valido) {
            header('Location: ../admin.php?erro=' . $motivo . '#agenda');
            exit;
        }
    }

    $stmt = $pdo->prepare("
        UPDATE agendamentos
        SET status = :status
        WHERE id = :id
    ");

    $stmt->execute([
        ':status' => $status,
        ':id' => $agendamentoId,
    ]);

    $ancora = $status === 'PENDENTE' ? '#pedidos-agendamento' : '#agenda';
    header('Location: ../admin.php?status=sucesso' . $ancora);
    exit;
} catch (PDOException $e) {
    header('Location: ../admin.php?erro=' . erroBancoParaCodigo($e->getMessage()) . '#agenda');
    exit;
}

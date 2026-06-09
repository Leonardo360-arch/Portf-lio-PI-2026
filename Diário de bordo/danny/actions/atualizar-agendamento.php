<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/AgendamentoHelper.php';

use Danny\Database;
use function Danny\validarAgendamento;
use function Danny\erroBancoParaCodigo;

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
$clienteNome = trim($_POST['cliente_nome'] ?? '');
$fotografoId = (int) ($_POST['fotografo_id'] ?? 0);
$tipoServicoId = (int) ($_POST['tipo_servico_id'] ?? 0);
$cenarioId = $_POST['cenario_id'] !== '' ? (int) $_POST['cenario_id'] : null;
$data = $_POST['data'] ?? '';
$horaInicio = $_POST['hora_inicio'] ?? '';
$observacao = trim($_POST['observacao'] ?? '');
$valor = $_POST['valor'] !== '' ? (float) str_replace(',', '.', $_POST['valor']) : null;
$status = $_POST['status'] ?? 'CONFIRMADO';

if (
    $agendamentoId <= 0 ||
    $clienteNome === '' ||
    $fotografoId <= 0 ||
    $tipoServicoId <= 0 ||
    $data === '' ||
    $horaInicio === '' ||
    !in_array($status, ['PENDENTE', 'CONFIRMADO', 'RECUSADO', 'CANCELADO', 'CONCLUIDO'], true)
) {
    header('Location: ../admin.php?erro=campos#agenda');
    exit;
}

try {
    $pdo = Database::connection();

    $stmtAtual = $pdo->prepare("
        SELECT id, admin_id
        FROM agendamentos
        WHERE id = :id
        LIMIT 1
    ");
    $stmtAtual->execute([':id' => $agendamentoId]);
    $agendamentoAtual = $stmtAtual->fetch();

    if (!$agendamentoAtual) {
        header('Location: ../admin.php?erro=status#agenda');
        exit;
    }

    [$valido, $motivo] = validarAgendamento(
        $pdo,
        $fotografoId,
        $tipoServicoId,
        $cenarioId,
        $data,
        $horaInicio,
        $status,
        $agendamentoId
    );

    if (!$valido) {
        header('Location: ../editar-agendamento.php?id=' . $agendamentoId . '&erro=' . $motivo);
        exit;
    }

    $pdo->beginTransaction();

    $stmtCliente = $pdo->prepare("
        UPDATE admins
        SET nome = :nome,
            tipo_servico_id = :tipo_servico_id
        WHERE id = :id
          AND tipo = 'CLIENTE'
    ");

    $stmtCliente->execute([
        ':nome' => $clienteNome,
        ':tipo_servico_id' => $tipoServicoId,
        ':id' => $agendamentoAtual['admin_id'],
    ]);

    $stmt = $pdo->prepare("
        UPDATE agendamentos
        SET
            fotografo_id = :fotografo_id,
            tipo_servico_id = :tipo_servico_id,
            cenario_id = :cenario_id,
            data = :data,
            hora_inicio = :hora_inicio,
            observacao = :observacao,
            valor = :valor,
            status = :status
        WHERE id = :id
    ");

    $stmt->execute([
        ':fotografo_id' => $fotografoId,
        ':tipo_servico_id' => $tipoServicoId,
        ':cenario_id' => $cenarioId,
        ':data' => $data,
        ':hora_inicio' => $horaInicio,
        ':observacao' => $observacao !== '' ? $observacao : null,
        ':valor' => $valor,
        ':status' => $status,
        ':id' => $agendamentoId,
    ]);

    $pdo->commit();

    $destino = $status === 'PENDENTE' ? '../admin.php?agendamento=atualizado#pedidos-agendamento' : '../admin.php?agendamento=atualizado#agenda';
    header('Location: ' . $destino);
    exit;
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    header('Location: ../editar-agendamento.php?id=' . $agendamentoId . '&erro=' . erroBancoParaCodigo($e->getMessage()));
    exit;
}

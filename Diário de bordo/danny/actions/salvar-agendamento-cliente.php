<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/AgendamentoHelper.php';

use Danny\Database;
use function Danny\validarAgendamento;
use function Danny\erroBancoParaCodigo;

session_start();

if (!isset($_SESSION['cliente_id'])) {
    header('Location: ../login-cliente.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../cliente.php#solicitar');
    exit;
}

$clienteId = (int) $_SESSION['cliente_id'];
$fotografoId = (int) ($_POST['fotografo_id'] ?? 0);
$tipoServicoId = (int) ($_POST['tipo_servico_id'] ?? 0);
$cenarioId = $_POST['cenario_id'] !== '' ? (int) $_POST['cenario_id'] : null;
$data = $_POST['data'] ?? '';
$horaInicio = $_POST['hora_inicio'] ?? '';
$observacao = trim($_POST['observacao'] ?? '');
$status = 'PENDENTE';

if ($fotografoId <= 0 || $tipoServicoId <= 0 || $data === '' || $horaInicio === '') {
    header('Location: ../cliente.php?erro=campos#solicitar');
    exit;
}

try {
    $pdo = Database::connection();

    [$valido, $motivo] = validarAgendamento($pdo, $fotografoId, $tipoServicoId, $cenarioId, $data, $horaInicio, $status);

    if (!$valido) {
        header('Location: ../cliente.php?erro=' . $motivo . '#solicitar');
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO agendamentos
        (admin_id, fotografo_id, tipo_servico_id, cenario_id, data, hora_inicio, observacao, status)
        VALUES
        (:admin_id, :fotografo_id, :tipo_servico_id, :cenario_id, :data, :hora_inicio, :observacao, 'PENDENTE')
    ");

    $stmt->execute([
        ':admin_id' => $clienteId,
        ':fotografo_id' => $fotografoId,
        ':tipo_servico_id' => $tipoServicoId,
        ':cenario_id' => $cenarioId,
        ':data' => $data,
        ':hora_inicio' => $horaInicio,
        ':observacao' => $observacao !== '' ? $observacao : null,
    ]);

    header('Location: ../cliente.php?agendamento=sucesso#meus-agendamentos');
    exit;
} catch (PDOException $e) {
    header('Location: ../cliente.php?erro=' . erroBancoParaCodigo($e->getMessage()) . '#solicitar');
    exit;
}

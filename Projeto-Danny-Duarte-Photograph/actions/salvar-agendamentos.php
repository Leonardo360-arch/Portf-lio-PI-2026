<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/AgendamentoHelper.php';

use Danny\Database;
use function Danny\buscarOuCriarCliente;
use function Danny\validarAgendamento;
use function Danny\erroBancoParaCodigo;


function normalizarTelefone(?string $telefone): ?string
{
    $numeros = preg_replace('/\D+/', '', (string) $telefone);

    if ($numeros === '') {
        return null;
    }

    if (strlen($numeros) !== 11) {
        return null;
    }

    return sprintf(
        '(%s) %s-%s',
        substr($numeros, 0, 2),
        substr($numeros, 2, 5),
        substr($numeros, 7, 4)
    );
}

function telefoneValidoOuVazio(?string $telefone): bool
{
    $numeros = preg_replace('/\D+/', '', (string) $telefone);

    return $numeros === '' || strlen($numeros) === 11;
}


session_start();

if (empty($_SESSION['admin_autorizado'])) {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin.php');
    exit;
}

$clienteNome = trim($_POST['cliente_nome'] ?? '');
$clienteTelefone = trim($_POST['cliente_telefone'] ?? '');
$clienteEmail = trim($_POST['cliente_email'] ?? '');
$fotografoId = (int) ($_POST['fotografo_id'] ?? 0);
$tipoServicoId = (int) ($_POST['tipo_servico_id'] ?? 0);
$cenarioId = $_POST['cenario_id'] !== '' ? (int) $_POST['cenario_id'] : null;
$data = $_POST['data'] ?? '';
$horaInicio = $_POST['hora_inicio'] ?? '';
$observacao = trim($_POST['observacao'] ?? '');
$valor = $_POST['valor'] !== '' ? (float) str_replace(',', '.', $_POST['valor']) : null;
$status = $_POST['status'] ?? 'CONFIRMADO';

if (!telefoneValidoOuVazio($clienteTelefone)) {
    header('Location: ../admin.php?erro=telefone#novo-agendamento');
    exit;
}

if ($clienteEmail !== '' && !filter_var($clienteEmail, FILTER_VALIDATE_EMAIL)) {
    header('Location: ../admin.php?erro=email#novo-agendamento');
    exit;
}

$clienteTelefone = normalizarTelefone($clienteTelefone);

if (!in_array($status, ['PENDENTE', 'CONFIRMADO'], true)) {
    $status = 'CONFIRMADO';
}

if ($clienteNome === '' || $fotografoId <= 0 || $tipoServicoId <= 0 || $data === '' || $horaInicio === '') {
    header('Location: ../admin.php?erro=campos#novo-agendamento');
    exit;
}

try {
    $pdo = Database::connection();

    [$valido, $motivo] = validarAgendamento($pdo, $fotografoId, $tipoServicoId, $cenarioId, $data, $horaInicio, $status);

    if (!$valido) {
        header('Location: ../admin.php?erro=' . $motivo . '#novo-agendamento');
        exit;
    }

    $pdo->beginTransaction();

    $clienteId = buscarOuCriarCliente($pdo, $clienteNome, $tipoServicoId, $clienteTelefone, $clienteEmail);

    $stmt = $pdo->prepare("
        INSERT INTO agendamentos
        (admin_id, fotografo_id, tipo_servico_id, cenario_id, data, hora_inicio, observacao, valor, status)
        VALUES
        (:admin_id, :fotografo_id, :tipo_servico_id, :cenario_id, :data, :hora_inicio, :observacao, :valor, :status)
    ");

    $stmt->execute([
        ':admin_id' => $clienteId,
        ':fotografo_id' => $fotografoId,
        ':tipo_servico_id' => $tipoServicoId,
        ':cenario_id' => $cenarioId,
        ':data' => $data,
        ':hora_inicio' => $horaInicio,
        ':observacao' => $observacao !== '' ? $observacao : null,
        ':valor' => $valor,
        ':status' => $status,
    ]);

    $pdo->commit();

    $destino = $status === 'PENDENTE' ? '../admin.php?agendamento=sucesso#pedidos-agendamento' : '../admin.php?agendamento=sucesso#agenda';
    header('Location: ' . $destino);
    exit;
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    header('Location: ../admin.php?erro=' . erroBancoParaCodigo($e->getMessage()) . '#novo-agendamento');
    exit;
}

<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

use Danny\Database;


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

if (!isset($_SESSION['cliente_id'])) {
    header('Location: ../login-cliente.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../cliente.php#perfil-cliente');
    exit;
}

function colunaExiste(PDO $pdo, string $tabela, string $coluna): bool
{
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = :tabela
          AND COLUMN_NAME = :coluna
    ");

    $stmt->execute([
        ':tabela' => $tabela,
        ':coluna' => $coluna,
    ]);

    return (int) $stmt->fetchColumn() > 0;
}

$clienteId = (int) $_SESSION['cliente_id'];
$clienteNome = trim($_POST['cliente_nome'] ?? '');
$clienteTelefone = trim($_POST['cliente_telefone'] ?? '');
$clienteEmail = trim($_POST['cliente_email'] ?? '');
$nichoId = $_POST['nicho_id'] !== '' ? (int) $_POST['nicho_id'] : null;
$tipoServicoId = (int) ($_POST['tipo_servico_id'] ?? 0);

if (!telefoneValidoOuVazio($clienteTelefone)) {
    header('Location: ../cliente.php?erro=telefone#perfil-cliente');
    exit;
}

if ($clienteEmail !== '' && !filter_var($clienteEmail, FILTER_VALIDATE_EMAIL)) {
    header('Location: ../cliente.php?erro=email#perfil-cliente');
    exit;
}

$clienteTelefone = normalizarTelefone($clienteTelefone);

if ($clienteNome === '' || $nichoId === null || $tipoServicoId <= 0) {
    header('Location: ../cliente.php?erro=campos#perfil-cliente');
    exit;
}

try {
    $pdo = Database::connection();

    $stmtServico = $pdo->prepare("
        SELECT id
        FROM tipos_servico
        WHERE id = :id
          AND nicho_id = :nicho_id
        LIMIT 1
    ");

    $stmtServico->execute([
        ':id' => $tipoServicoId,
        ':nicho_id' => $nichoId,
    ]);

    if (!$stmtServico->fetch()) {
        header('Location: ../cliente.php?erro=servico#perfil-cliente');
        exit;
    }

    $sets = [
        'nome = :nome',
        'nicho_id = :nicho_id',
        'tipo_servico_id = :tipo_servico_id',
        'email = :email',
    ];

    $params = [
        ':nome' => $clienteNome,
        ':nicho_id' => $nichoId,
        ':tipo_servico_id' => $tipoServicoId,
        ':email' => $clienteEmail !== '' ? $clienteEmail : 'cliente_' . $clienteId . '@sememail.local',
        ':id' => $clienteId,
    ];

    if (colunaExiste($pdo, 'admins', 'telefone')) {
        $sets[] = 'telefone = :telefone';
        $params[':telefone'] = $clienteTelefone !== '' ? $clienteTelefone : 'Não informado';
    }

    $stmt = $pdo->prepare("
        UPDATE admins
        SET " . implode(', ', $sets) . "
        WHERE id = :id
          AND tipo = 'CLIENTE'
    ");

    $stmt->execute($params);

    $_SESSION['cliente_nome'] = $clienteNome;

    header('Location: ../cliente.php?perfil=atualizado#perfil-cliente');
    exit;
} catch (PDOException $e) {
    header('Location: ../cliente.php?erro=banco#perfil-cliente');
    exit;
}

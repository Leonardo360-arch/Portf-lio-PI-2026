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

if (empty($_SESSION['admin_autorizado'])) {
    header('Location: ../login.php');
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

function garantirColuna(PDO $pdo, string $tabela, string $coluna, string $definicao): void
{
    if (!colunaExiste($pdo, $tabela, $coluna)) {
        $pdo->exec("ALTER TABLE `{$tabela}` ADD COLUMN `{$coluna}` {$definicao}");
    }
}

$id = (int) ($_POST['id'] ?? 0);
$nome = trim($_POST['nome'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');
$email = trim($_POST['email'] ?? '');

if (!telefoneValidoOuVazio($telefone)) {
    header('Location: ../servicos-cadastrados.php?erro=telefone#fotografos-lista');
    exit;
}

$telefone = normalizarTelefone($telefone) ?? '';

if ($nome === '') {
    header('Location: ../servicos-cadastrados.php?erro=campos#fotografos-lista');
    exit;
}

try {
    $pdo = Database::connection();

    garantirColuna($pdo, 'fotografos', 'telefone', "VARCHAR(20) DEFAULT NULL");
    garantirColuna($pdo, 'fotografos', 'email', "VARCHAR(150) DEFAULT NULL");

    if ($id > 0) {
        $stmt = $pdo->prepare("
            UPDATE fotografos
            SET nome = :nome,
                telefone = :telefone,
                email = :email
            WHERE id = :id
        ");

        $stmt->execute([
            ':nome' => $nome,
            ':telefone' => $telefone !== '' ? $telefone : null,
            ':email' => $email !== '' ? $email : null,
            ':id' => $id,
        ]);
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO fotografos
            (nome, telefone, email)
            VALUES
            (:nome, :telefone, :email)
        ");

        $stmt->execute([
            ':nome' => $nome,
            ':telefone' => $telefone !== '' ? $telefone : null,
            ':email' => $email !== '' ? $email : null,
        ]);
    }

    header('Location: ../servicos-cadastrados.php?opcao=sucesso#fotografos-lista');
    exit;
} catch (PDOException $e) {
    header('Location: ../servicos-cadastrados.php?erro=banco#fotografos-lista');
    exit;
}

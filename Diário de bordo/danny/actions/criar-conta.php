<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

use Danny\Database;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../criar-conta.php');
    exit;
}

$nome = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');
$senha = $_POST['senha'] ?? '';
$confirmarSenha = $_POST['confirmar_senha'] ?? '';

if ($nome === '' || $email === '' || $senha === '' || $confirmarSenha === '') {
    header('Location: ../criar-conta.php?erro=campos');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ../criar-conta.php?erro=email');
    exit;
}

if ($senha !== $confirmarSenha) {
    header('Location: ../criar-conta.php?erro=senha');
    exit;
}

try {
    $pdo = Database::connection();

    $stmt = $pdo->prepare("
        INSERT INTO admins (nome, email, telefone, senha_hash) VALUES (:nome, :email, :telefone, :senha_hash)
    ");

    $stmt->execute([
        ':nome' => $nome,
        ':email' => $email,
        ':telefone' => $telefone !== '' ? $telefone : null,
        ':senha_hash' => password_hash($senha, PASSWORD_DEFAULT),
    ]);

    header('Location: ../login.php?cadastro=sucesso');
    exit;
} catch (PDOException $e) {
    header('Location: ../criar-conta.php?erro=banco');
    exit;
}

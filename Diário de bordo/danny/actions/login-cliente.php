<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

use Danny\Database;

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login-cliente.php');
    exit;
}

$email = trim($_POST['email'] ?? '');
$senha = $_POST['senha'] ?? '';

if ($email === '' || $senha === '') {
    header('Location: ../login-cliente.php?erro=campos');
    exit;
}

try {
    $pdo = Database::connection();

    $stmt = $pdo->prepare("
        SELECT id, nome, email, senha_hash
        FROM admins
        WHERE email = :email
          AND tipo = 'CLIENTE'
        LIMIT 1
    ");

    $stmt->execute([':email' => $email]);
    $cliente = $stmt->fetch();

    if (!$cliente || !password_verify($senha, $cliente['senha_hash'])) {
        header('Location: ../login-cliente.php?erro=login');
        exit;
    }

    $_SESSION['cliente_id'] = $cliente['id'];
    $_SESSION['cliente_nome'] = $cliente['nome'];
    $_SESSION['cliente_email'] = $cliente['email'];

    header('Location: ../cliente.php');
    exit;
} catch (PDOException $e) {
    header('Location: ../login-cliente.php?erro=banco');
    exit;
}

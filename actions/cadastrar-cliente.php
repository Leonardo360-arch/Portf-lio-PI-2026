<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

use Danny\Database;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../cadastro-cliente.php');
    exit;
}

$nome = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');
$nichoId = $_POST['nicho_id'] !== '' ? $_POST['nicho_id'] : null;
$tipoServicoId = $_POST['tipo_servico_id'] !== '' ? $_POST['tipo_servico_id'] : null;
$senha = $_POST['senha'] ?? '';
$confirmarSenha = $_POST['confirmar_senha'] ?? '';

if ($nome === '' || $email === '' || $telefone === '' || $senha === '' || $confirmarSenha === '') {
    header('Location: ../cadastro-cliente.php?erro=campos');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ../cadastro-cliente.php?erro=email');
    exit;
}

if ($senha !== $confirmarSenha) {
    header('Location: ../cadastro-cliente.php?erro=senha');
    exit;
}

try {
    $pdo = Database::connection();

    $check = $pdo->prepare('SELECT id FROM admins WHERE email = :email LIMIT 1');
    $check->execute([':email' => $email]);

    if ($check->fetch()) {
        header('Location: ../cadastro-cliente.php?erro=existe');
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO admins (nome, email, telefone, senha_hash, senha_provisoria, nicho_id, tipo_servico_id, tipo) VALUES (:nome, :email, :telefone, :senha_hash, 0, :nicho_id, :tipo_servico_id, 'CLIENTE')
    ");

    $stmt->execute([
        ':nome' => $nome,
        ':email' => $email,
        ':telefone' => $telefone,
        ':senha_hash' => password_hash($senha, PASSWORD_DEFAULT),
        ':nicho_id' => $nichoId,
        ':tipo_servico_id' => $tipoServicoId,
    ]);

    header('Location: ../login-cliente.php?cadastro=sucesso');
    exit;
} catch (PDOException $e) {
    header('Location: ../cadastro-cliente.php?erro=banco');
    exit;
}
